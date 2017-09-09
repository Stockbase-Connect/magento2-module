<?php


namespace Stockbase\Integration\Model\ResourceModel\StockItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Stockbase\Integration\Model\ResourceModel\StockItem as StockItemResource;
use Stockbase\Integration\Model\StockItem;

/**
 * StockItem ollection
 */
class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(StockItem::class, StockItemResource::class);
    }
}
