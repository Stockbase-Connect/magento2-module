<?php
namespace Strategery\Stockbase\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
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
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getInvoice();

        $order = $invoice->getOrder();
        
        if ($this->stockbaseConfiguration->isModuleEnabled()) {
            $quoteItemIds = array_map(function (OrderItem $item) {
                return $item->getQuoteItemId();
            }, (array) $order->getAllItems());

            /** @var StockItemReserve[] $reservedStockbaseItems */
            $reservedStockbaseItems = $this->stockbaseStockManagement->getReserveForQuoteItem($quoteItemIds);
            if (!empty($reservedStockbaseItems)) {
                $client = $this->stockbaseClientFactory->create();
                $result = $client->createOrder($order, $reservedStockbaseItems);

                foreach ($reservedStockbaseItems as $reserve) {
                    $order->addStatusHistoryComment(__(
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
                    
                    $order->addStatusHistoryComment(__(
                        'Local Stockbase stock index for item with EAN "%1" has been updated.',
                        $reserve->getEan()
                    ));
                    //$order->save();
                }
            }
        }
        
        return $this;
    }
}
