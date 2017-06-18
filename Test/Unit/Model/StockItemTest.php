<?php


namespace Stockbase\Integration\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\StockItem;

/**
 * Class StockItemTest
 */
class StockItemTest extends TestCase
{
    /** @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    private $registry;
    
    /** @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\Model\Context::class);
        $this->registry = $this->createMock(\Magento\Framework\Registry::class);
        $this->resource = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class);
        $this->resource->method('getIdFieldName')->willReturn('id');
    }

    /**
     * testGettersAndSetters
     * @dataProvider gettersAndSettersProvider
     *
     * @param mixed $propertyName
     * @param mixed $fieldName
     */
    public function testGettersAndSetters($propertyName, $fieldName)
    {
        $model = $this->createModel();
        
        $value = uniqid();
        $model->{'set'.$propertyName}($value);
        $this->assertEquals($value, $model->getData($fieldName));
        $this->assertEquals($value, $model->{'get'.$propertyName}());
    }

    /**
     * @return array
     */
    public function gettersAndSettersProvider()
    {
        return [
            ['Ean', 'id'],
            ['Brand', 'brand'],
            ['Code', 'code'],
            ['SupplierCode', 'supplier_code'],
            ['SupplierGln', 'supplier_gln'],
            ['Amount', 'amount'],
            ['Noos', 'noos'],
            ['Timestamp', 'timestamp'],
        ];
    }
    
    protected function createModel()
    {
        return new StockItem(
            $this->context,
            $this->registry,
            $this->resource
        );
    }
}
