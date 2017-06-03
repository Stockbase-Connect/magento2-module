<?php
namespace Strategery\Stockbase\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Strategery\Stockbase\StockbaseApi\Client\StockbaseClientFactory;
use Strategery\Stockbase\Model\Config\StockbaseConfiguration;
use Strategery\Stockbase\Model\Inventory\StockbaseStockManagement;
use Strategery\Stockbase\Model\OrderedItem as StockbaseOrderedItem;
use Strategery\Stockbase\Model\StockItemReserve;

/**
 * Class OrderPaymentPayObserver
 */
class OrderPaymentPayObserver implements ObserverInterface
{
    /**
     * @var StockbaseConfiguration
     */
    private $stockbaseConfiguration;

    /**
     * @var StockbaseClientFactory
     */
    private $stockbaseClientFactory;

    /**
     * @var StockbaseStockManagement
     */
    private $stockbaseStockManagement;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * OrderPaymentPayObserver constructor.
     * @param StockbaseConfiguration   $stockbaseConfiguration
     * @param StockbaseStockManagement $stockbaseStockManagement
     * @param StockbaseClientFactory   $stockbaseClientFactory
     * @param ObjectManagerInterface   $objectManager
     */
    public function __construct(
        StockbaseConfiguration $stockbaseConfiguration,
        StockbaseStockManagement $stockbaseStockManagement,
        StockbaseClientFactory $stockbaseClientFactory,
        ObjectManagerInterface $objectManager
    ) {
        $this->stockbaseConfiguration = $stockbaseConfiguration;
        $this->stockbaseClientFactory = $stockbaseClientFactory;
        $this->stockbaseStockManagement = $stockbaseStockManagement;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->stockbaseConfiguration->isModuleEnabled()) {
            return $this;
        }

        /** @var \Magento\Sales\Api\Data\InvoiceInterface $invoice */
        $invoice = $observer->getData('invoice');

        // since InvoiceInterface doesn't explicitly define getOrder(), we have to do this...
        if (\method_exists($invoice, 'getOrder')) {
            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $invoice->getOrder();
        } else {
            /** @var OrderRepositoryInterface $repository */
            $repository = $this->objectManager->get(OrderRepositoryInterface::class);
            $order = $repository->get($invoice->getOrderId());
        }

        $quoteItemIds = \array_map(function (OrderItemInterface $item) {
            return $item->getQuoteItemId();
        }, (array) $order->getAllItems());

        /** @var StockItemReserve[] $reservedStockbaseItems */
        $reservedStockbaseItems = $this->stockbaseStockManagement->getReserveForQuoteItem($quoteItemIds);

        if (empty($reservedStockbaseItems)) {
            return $this;
        }

        $client = $this->stockbaseClientFactory->create();
        $result = $client->createOrder($order, $reservedStockbaseItems);

        foreach ($reservedStockbaseItems as $reserve) {
            $this->addStatusHistoryComment($order, __(
                'Item with EAN "%1" (%2 pc.) has been ordered from Stockbase.',
                $reserve->getEan(),
                $reserve->getAmount()
            ));
            //$order->save();

            /** @var StockbaseOrderedItem $stockbaseOrderedItem */
            $stockbaseOrderedItem = $this->objectManager->create(StockbaseOrderedItem::class);
            $stockbaseOrderedItem->setOrderId($order->getId());
            $stockbaseOrderedItem->setOrderItemId($reserve->getOrderItemId());
            $stockbaseOrderedItem->setProductId($reserve->getProductId());
            $stockbaseOrderedItem->setEan($reserve->getEan());
            $stockbaseOrderedItem->setAmount($reserve->getAmount());
            $stockbaseOrderedItem->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));
            $stockbaseOrderedItem->save();

            // Decrement local Stockbase stock amount
            $this->stockbaseStockManagement->updateStockAmount($reserve->getEan(), $reserve->getAmount(), '-');

            // Release the reserve
            $this->stockbaseStockManagement->releaseReserve($reserve);

            $this->addStatusHistoryComment($order, __(
                'Local Stockbase stock index for item with EAN "%1" has been updated.',
                $reserve->getEan()
            ));
            //$order->save();
        }

        return $this;
    }

    /**
     * Safe add status history comment.
     *
     * @param OrderInterface $order
     * @param string $comment
     * @param $status
     * @return void
     */
    private function addStatusHistoryComment(OrderInterface $order, string $comment, $status = false)
    {
        if (!$order instanceof \Magento\Sales\Model\Order) {
            return;
        }

        $order->addStatusHistoryComment($comment, $status);
    }
}
