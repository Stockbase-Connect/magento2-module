<?php

namespace Stockbase\Integration\Model\ResourceModel\StockItemReserve;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Stockbase\Integration\Model\ResourceModel\StockItemReserve as StockItemReserveResource;
use Stockbase\Integration\Model\StockItemReserve;

/**
 * StockItemReserve collection
 */
class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(StockItemReserve::class, StockItemReserveResource::class);
    }
}
