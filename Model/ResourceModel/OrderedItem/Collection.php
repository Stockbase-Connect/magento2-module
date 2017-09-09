<?php

namespace Stockbase\Integration\Model\ResourceModel\OrderedItem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Stockbase\Integration\Model\OrderedItem;
use Stockbase\Integration\Model\ResourceModel\OrderedItem as OrderedItemResource;

/**
 * OrderedItem collection
 */
class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(OrderedItem::class, OrderedItemResource::class);
    }
}
