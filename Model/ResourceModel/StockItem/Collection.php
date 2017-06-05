<?php


namespace Stockbase\Integration\Model\ResourceModel\StockItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Stockbase\Integration\Model\StockItem::class,
            \Stockbase\Integration\Model\ResourceModel\StockItem::class
        );
    }
}
