<?php


namespace Strategery\Stockbase\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Strategery\Stockbase\Model\Inventory\StockbaseStockManagement;

class CancelOrderItemObserver extends \Magento\CatalogInventory\Observer\CancelOrderItemObserver
{
    /**
     * @var StockbaseStockManagement
     */
    private $stockbaseStockManagement;

    /**
     * @param StockManagementInterface $stockManagement
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     */
    public function __construct(
        StockManagementInterface $stockManagement,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        StockbaseStockManagement $stockbaseStockManagement
    ) {
        parent::__construct($stockManagement, $priceIndexer);

        $this->stockbaseStockManagement = $stockbaseStockManagement;
    }

    /**
     * Cancel order item
     *
     * @param   EventObserver $observer
     * @return  void
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
                if ($qty != $reserve->getMagentoStockAmount()) {
                    //throw new \Exception('Invalid quote inventory revert.'); //TODO: Enable additional checks
                }
                $this->stockbaseStockManagement->releaseReserve($reserve);
            }
            $this->stockManagement->backItemQty($item->getProductId(), $qty, $item->getStore()->getWebsiteId());
        }
        $this->priceIndexer->reindexRow($item->getProductId());
    }
}