<?php

namespace Stockbase\Integration\Test\Unit\Plugin\Stock;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Plugin\Stock\StatusPlugin;

/**
 * Class StatusPluginTest
 */
class StatusPluginTest extends TestCase
{
    /**
     * Test if the "is_salable" column in the query is successfully replaced
     */
    public function testReplacesIsSalable()
    {
        $this->markTestIncomplete();
        /** @var Status $stockStatus */
        $stockStatus = $this->createMock(Status::class);
        /** @var ProductCollection $collection */
        $collection = $this->createMock(ProductCollection::class);
        $instance = new StatusPlugin();
        $instance->afterAddStockDataToCollection($stockStatus, $collection, true);
    }
}
