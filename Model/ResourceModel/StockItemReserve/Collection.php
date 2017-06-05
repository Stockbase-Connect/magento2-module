<?php


namespace Stockbase\Integration\Model\ResourceModel\StockItemReserve;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Stockbase\Integration\Model\StockItemReserve::class,
            \Stockbase\Integration\Model\ResourceModel\StockItemReserve::class
        );
    }
}
