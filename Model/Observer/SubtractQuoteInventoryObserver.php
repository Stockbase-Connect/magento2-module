<?php


namespace Stockbase\Integration\Model\Observer;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\CatalogInventory\Observer\ProductQty;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;

/**
 * Class SubtractQuoteInventoryObserver
 */
class SubtractQuoteInventoryObserver extends \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;
    
    /**
     * @var StockbaseStockManagement
     */
    private $stockbaseStockManagement;

    /**
     * SubtractQuoteInventoryObserver constructor.
     * @param StockManagementInterface $stockManagement
     * @param ProductQty               $productQty
     * @param ItemsForReindex          $itemsForReindex
     * @param StockRegistryInterface   $stockRegistry
     * @param StockbaseStockManagement $stockbaseStockManagement
     */
    public function __construct(
        StockManagementInterface $stockManagement,
        ProductQty $productQty,
        ItemsForReindex $itemsForReindex,
        StockRegistryInterface $stockRegistry,
        StockbaseStockManagement $stockbaseStockManagement
    ) {
        parent::__construct($stockManagement, $productQty, $itemsForReindex);
        $this->stockRegistry = $stockRegistry;
        $this->stockbaseStockManagement = $stockbaseStockManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        
        // Maybe we've already processed this quote in some event during order placement
        // e.g. call in event 'sales_model_service_quote_submit_before' and later in 'checkout_submit_all_after'
        if ($quote->getInventoryProcessed()) {
            return $this;
        }

        $items = [];
        foreach ($quote->getAllItems() as $item) {
            $productId = $item->getProductId();
            if (!$productId) {
                continue;
            }
            $children = $item->getChildrenItems() ?: [$item];
            foreach ($children as $childItem) {
                $productId = $childItem->getProductId();
                if (!$productId) {
                    continue;
                }
                
                if (!isset($items[$productId])) {
                    $items[$productId] = 0;
                }
                $items[$productId] += $childItem->getTotalQty();

                $stockItem = $this->stockRegistry->getStockItem($productId);
                if ($this->stockbaseStockManagement->isStockbaseProduct($productId) &&
                    $items[$productId] > $stockItem->getQty()
                ) {
                    $diff = $items[$productId] - $stockItem->getQty();
                    $items[$productId] = $stockItem->getQty();
                    
                    $stockbaseAmount = $this->stockbaseStockManagement->getStockbaseStockAmount($productId);
                    if ($stockbaseAmount - $diff < 0) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Not all of your products are available in the requested quantity.')
                        );
                    }

                    $reserveItem = $this->stockbaseStockManagement->createReserve($childItem, $diff, $childItem->getTotalQty() - $diff);
                    $order->addStatusHistoryComment(__(
                        'Local Stockbase reserve created for EAN "%1" (%2 pc.)',
                        $reserveItem->getEan(),
                        $reserveItem->getAmount()
                    ));
                }
            }
        }
        
        /**
         * Remember items
         */
        $itemsForReindex = $this->stockManagement->registerProductsSale(
            $items,
            $quote->getStore()->getWebsiteId()
        );
        $this->itemsForReindex->setItems($itemsForReindex);

        $quote->setInventoryProcessed(true);
        
        return $this;
    }
}
