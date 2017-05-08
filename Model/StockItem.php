<?php


namespace Strategery\Stockbase\Model;

use Magento\Framework\Model\AbstractModel;

class StockItem extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Strategery\Stockbase\Model\ResourceModel\StockItem::class);
    }
}
