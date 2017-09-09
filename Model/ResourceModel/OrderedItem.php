<?php

namespace Stockbase\Integration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Archives an item that was ordered through StockBase
 */
class OrderedItem extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('stockbase_ordered_item', 'id');
    }
}
