<?php
namespace Strategery\Stockbase\Model\Observer;


use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Strategery\Stockbase\Api\Client\StockbaseClientFactory;
use Strategery\Stockbase\Model\Config\StockbaseConfiguration;
use Strategery\Stockbase\Model\ResourceModel\StockItemReserve\Collection as StockItemReserveCollection;
use Strategery\Stockbase\Model\ResourceModel\StockItem as StockItemResource;
use Strategery\Stockbase\Model\StockItemReserve;

class OrderPaymentPayObserver implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var StockbaseConfiguration
     */
    private $stockbaseConfiguration;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var StockbaseClientFactory
     */
    private $stockbaseClientFactory;

    public function __construct(
        StockbaseConfiguration $stockbaseConfiguration,
        ObjectManagerInterface $objectManager,
        StockbaseClientFactory $stockbaseClientFactory
    ) {
        $this->stockbaseConfiguration = $stockbaseConfiguration;
        $this->objectManager = $objectManager;
        $this->stockbaseClientFactory = $stockbaseClientFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getInvoice();

        $order = $invoice->getOrder();
        
        if ($this->stockbaseConfiguration->isModuleEnabled()) {

            $quoteItemIds = array_map(function(OrderItem $item) {
                return $item->getQuoteItemId();
            }, (array)$order->getAllItems());

            /** @var StockItemReserveCollection $reserveCollection */
            $reserveCollection = $this->objectManager->create(StockItemReserveCollection::class);
            $reserveCollection->addFieldToFilter('quote_item_id', ['in' => $quoteItemIds]);

            /** @var StockItemResource $stockItemResource */
            $stockItemResource = $this->objectManager->create(StockItemResource::class);

            /** @var StockItemReserve[] $reservedStockbaseItems */
            $reservedStockbaseItems = $reserveCollection->getItems();
            
            if (!empty($reservedStockbaseItems)) {

                $client = $this->stockbaseClientFactory->create();
                $client->createOrder($order, $reservedStockbaseItems);

                foreach ($reservedStockbaseItems as $reserve) {
                    $order->addStatusHistoryComment(__(
                        'Item with EAN "%1" (%2 pc.) has been ordered from Stockbase.',
                        $reserve->getEan(),
                        $reserve->getAmount()
                    ));
                    //$order->save();

                    // Decrement local Stockbase stock amount
                    $stockItemResource->updateStockAmount($reserve->getEan(), $reserve->getAmount(), '-');
                    
                    // Release the reserve
                    $reserve->delete();
                    
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
