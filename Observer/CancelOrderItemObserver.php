<?php

namespace Stockbase\Integration\Observer;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceProcessor;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;

/**
 * Class CancelOrderItemObserver
 */
class CancelOrderItemObserver extends \Magento\CatalogInventory\Observer\CancelOrderItemObserver
{
    /**
     * @var StockbaseStockManagement
     */
    private $stockbaseStockManagement;

    /**
     * @param StockManagementInterface $stockManagement
     * @param ProductPriceProcessor    $priceIndexer
     * @param StockbaseStockManagement $stockbaseStockManagement
     */
    public function __construct(
        StockManagementInterface $stockManagement,
        ProductPriceProcessor $priceIndexer,
        StockbaseStockManagement $stockbaseStockManagement
    ) {
        parent::__construct($stockManagement, $priceIndexer);

        $this->stockbaseStockManagement = $stockbaseStockManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        $item = $observer->getEvent()->getItem();

        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
        if ($item->getId() && $item->getProductId() && empty($children) && $qty) {
            $reserve = $this->stockbaseStockManagement->getReserveForQuoteItem($item->getQuoteItemId());
            if (!empty($reserve)) {
                $reserve = reset($reserve);
                $qty -= $reserve->getAmount();
                //if ($qty != $reserve->getMagentoStockAmount()) {
                //    throw new \Exception('Invalid quote inventory revert.'); //TODO: Enable additional checks
                //}
                $this->stockbaseStockManagement->releaseReserve($reserve);
            }
            $this->stockManagement->backItemQty($item->getProductId(), $qty, $item->getStore()->getWebsiteId());
        }
        $this->priceIndexer->reindexRow($item->getProductId());
    }
}
