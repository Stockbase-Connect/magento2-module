<?php


namespace Stockbase\Integration\Model\ResourceModel;

/**
 * Class OrderedItem
 */
class OrderedItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('stockbase_ordered_item', 'id');
    }
}
