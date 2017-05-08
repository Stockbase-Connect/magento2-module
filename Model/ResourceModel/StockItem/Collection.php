<?php


namespace Strategery\Stockbase\Model\ResourceModel\StockItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Strategery\Stockbase\Model\StockItem::class,
            \Strategery\Stockbase\Model\ResourceModel\StockItem::class
        );
    }
}
