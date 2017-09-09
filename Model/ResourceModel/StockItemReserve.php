<?php

namespace Stockbase\Integration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Represents an amount of stock from a Stockbase item, reserved for a future purchase
 */
class StockItemReserve extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('stockbase_stock_reserve', 'id');
    }
}
