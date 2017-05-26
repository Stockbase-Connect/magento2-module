<?php


namespace Strategery\Stockbase\Model\Observer;

use Magento\CatalogInventory\Observer\ProductQty;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Strategery\Stockbase\Model\Inventory\StockbaseStockManagement;

class RevertQuoteInventoryObserver extends \Magento\CatalogInventory\Observer\RevertQuoteInventoryObserver
{
    /**
     * @var StockbaseStockManagement
     */
    private $stockbaseStockManagement;

    public function __construct(
        ProductQty $productQty,
        StockManagementInterface $stockManagement,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        StockbaseStockManagement $stockbaseStockManagement
    ) {
        parent::__construct($productQty, $stockManagement, $stockIndexerProcessor, $priceIndexer);
        $this->stockbaseStockManagement = $stockbaseStockManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

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

                $amount = $childItem->getTotalQty();
                $reserve = $this->stockbaseStockManagement->getReserveForQuoteItem($childItem->getId());
                if (!empty($reserve)) {
                    $reserve = reset($reserve);
                    $amount -= $reserve->getAmount();
                    if ($amount != $reserve->getMagentoStockAmount()) {
                        //throw new \Exception('Invalid quote inventory revert.'); //TODO: Enable additional checks
                    }
                    $this->stockbaseStockManagement->releaseReserve($reserve);
                }
                if (!isset($items[$productId])) {
                    $items[$productId] = 0;
                }
                $items[$productId] += $amount;
            }
        }
        
        $this->stockManagement->revertProductsSale($items, $quote->getStore()->getWebsiteId());
        $productIds = array_keys($items);
        if (!empty($productIds)) {
            $this->stockIndexerProcessor->reindexList($productIds);
            $this->priceIndexer->reindexList($productIds);
        }
        // Clear flag, so if order placement retried again with success - it will be processed
        $quote->setInventoryProcessed(false);
    }
}