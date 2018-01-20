<?php

namespace Stockbase\Integration\Model\ResourceModel\ProductImage;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Stockbase\Integration\Model\ResourceModel\ProductImage
 */
class Collection extends AbstractCollection
{
    /**
     * Constructor.
     */
    protected function _construct()
    {
        $this->_init(
            \Stockbase\Integration\Model\ProductImage::class,
            \Stockbase\Integration\Model\ResourceModel\ProductImage::class
        );
    }
}
