<?php


namespace Strategery\Stockbase\Model\Observer;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\CatalogInventory\Observer\ProductQty;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Strategery\Stockbase\Model\Config\StockbaseConfiguration;
use Strategery\Stockbase\Model\Inventory\StockbaseStockManagement;
use Strategery\Stockbase\Model\StockItemFactory;

class SubtractQuoteInventoryObserver extends \Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;
//    /**
//     * @var StockbaseConfiguration
//     */
//    private $config;
//    /**
//     * @var ProductFactory
//     */
//    private $productFactory;
//    /**
//     * @var StockItemFactory
//     */
//    private $stockItemFactory;
    /**
     * @var StockbaseStockManagement
     */
    private $stockbaseStockManagement;

    /**
     * SubtractQuoteInventoryObserver constructor.
     * @param StockManagementInterface $stockManagement
     * @param ProductQty $productQty
     * @param ItemsForReindex $itemsForReindex
     */
    public function __construct(
        StockManagementInterface $stockManagement,
        ProductQty $productQty,
        ItemsForReindex $itemsForReindex,
        StockRegistryInterface $stockRegistry,
//        StockbaseConfiguration $config,
//        ProductFactory $productFactory,
//        StockItemFactory $stockItemFactory,
        StockbaseStockManagement $stockbaseStockManagement
    ) {
        parent::__construct($stockManagement, $productQty, $itemsForReindex);
        $this->stockRegistry = $stockRegistry;
//        $this->config = $config;
//        $this->productFactory = $productFactory;
//        $this->stockItemFactory = $stockItemFactory;
        $this->stockbaseStockManagement = $stockbaseStockManagement;
    }

    /**
     * Subtract quote items qtys from stock items related with quote items products.
     *
     * Used before order placing to make order save/place transaction smaller
     * Also called after every successful order placement to ensure subtraction of inventory
     *
     * @param EventObserver $observer
     * @return $this
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
                if (
                    $this->stockbaseStockManagement->isStockbaseProduct($productId) &&
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
                    
                    //TODO: RevertQuoteInventoryObserver
                    //TODO: Update order_item_id on reserve item after successful order placement
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


//    /**
//     * Prepare array with information about used product qty and product stock item
//     *
//     * @param array $relatedItems
//     * @return array
//     */
//    public function getProductQty($relatedItems)
//    {
//        $items = [];
//        foreach ($relatedItems as $item) {
//            $productId = $item->getProductId();
//            if (!$productId) {
//                continue;
//            }
//            $children = $item->getChildrenItems();
//            if ($children) {
//                foreach ($children as $childItem) {
//                    $this->_addItemToQtyArray($childItem, $items);
//                }
//            } else {
//                $this->_addItemToQtyArray($item, $items);
//            }
//        }
//        return $items;
//    }
//
//    /**
//     * Adds stock item qty to $items (creates new entry or increments existing one)
//     *
//     * @param QuoteItem $quoteItem
//     * @param array &$items
//     * @return void
//     */
//    protected function _addItemToQtyArray(QuoteItem $quoteItem, &$items)
//    {
//        $productId = $quoteItem->getProductId();
//        if (!$productId) {
//            return;
//        }
//        if (isset($items[$productId])) {
//            $items[$productId] += $quoteItem->getTotalQty();
//        } else {
//            $items[$productId] = $quoteItem->getTotalQty();
//        }
//    }
    
}
