<?php


namespace Strategery\Stockbase\Model\ResourceModel;

class StockItemReserve extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('stockbase_stock_reserve', 'id');
    }
}
