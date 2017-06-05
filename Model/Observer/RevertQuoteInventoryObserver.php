<?php


namespace Stockbase\Integration\Model\Observer;

use Magento\CatalogInventory\Observer\ProductQty;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as IndexStockProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceProcessor;
use Magento\Framework\Event\Observer as EventObserver;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;

/**
 * Class RevertQuoteInventoryObserver
 */
class RevertQuoteInventoryObserver extends \Magento\CatalogInventory\Observer\RevertQuoteInventoryObserver
{
    /**
     * @var StockbaseStockManagement
     */
    private $stockbaseStockManagement;

    /**
     * RevertQuoteInventoryObserver constructor.
     * @param ProductQty               $productQty
     * @param StockManagementInterface $stockManagement
     * @param IndexStockProcessor      $stockIndexerProcessor
     * @param ProductPriceProcessor    $priceIndexer
     * @param StockbaseStockManagement $stockbaseStockManagement
     */
    public function __construct(
        ProductQty $productQty,
        StockManagementInterface $stockManagement,
        IndexStockProcessor $stockIndexerProcessor,
        ProductPriceProcessor $priceIndexer,
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
                    //if ($amount != $reserve->getMagentoStockAmount()) {
                    //    throw new \Exception('Invalid quote inventory revert.'); //TODO: Enable additional checks
                    //}
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
