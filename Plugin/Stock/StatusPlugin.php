<?php

namespace Stockbase\Integration\Plugin\Stock;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;

/**
 * Adds information about the Stockbase stock to collections
 */
class StatusPlugin
{
    /**
     * @param Status     $stockStatus
     * @param Collection $collection
     * @param bool       $isFilterInStock
     * @return Collection
     */
    public function afterAddStockDataToCollection(Status $stockStatus, $collection, $isFilterInStock)
    {
        /** @var string $eanAttribute */
        $eanAttribute = $this->getEanAttribute();

        $collection->addAttributeToSelect(['ean' => $eanAttribute]);

        $collection->getSelect()->joinLeft(
            ['s' => $this->getStockbaseStockTable()],
            'ean = s.ean',
            ['is_salable' => new \Zend_Db_Expr('IF(s.noos = 1, 1000000000, s.amount - COALESCE(SUM(r.amount), 0)) > 0')]
        );

        return $collection;
    }

    /**
     * @return string
     */
    private function getEanAttribute()
    {
        return 'ean'; // TODO: get from config
    }

    /**
     * @return string
     */
    private function getStockbaseStockTable()
    {
        return 'stockbase_stock'; // TODO: get from resource
    }
}
