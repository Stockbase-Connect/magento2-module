<?php


namespace Stockbase\Integration\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;

/**
 * Class SalesOrderAfterPlaceObserver
 */
class SalesOrderAfterPlaceObserver implements ObserverInterface
{
    /**
     * @var StockbaseStockManagement
     */
    private $stockbaseStockManagement;

    /**
     * SalesOrderAfterPlaceObserver constructor.
     * @param StockbaseStockManagement $stockbaseStockManagement
     */
    public function __construct(
        StockbaseStockManagement $stockbaseStockManagement
    ) {
        $this->stockbaseStockManagement = $stockbaseStockManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        foreach ($order->getAllItems() as $item) {
            $reserve = $this->stockbaseStockManagement->getReserveForQuoteItem($item->getQuoteItemId());
            if (!empty($reserve)) {
                $reserve = reset($reserve);

                $reserve->setOrderItemId($item->getId());
                $reserve->save();
            }
        }
    }
}
