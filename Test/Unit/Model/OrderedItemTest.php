<?php


namespace Stockbase\Integration\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\OrderedItem;

/**
 * Class OrderedItemTest
 */
class OrderedItemTest extends TestCase
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
            ['OrderId', 'order_id'],
            ['OrderItemId', 'order_item_id'],
            ['ProductId', 'product_id'],
            ['Ean', 'ean'],
            ['Amount', 'amount'],
            ['StockbaseGuid', 'stockbase_guid'],
            ['CreatedAt', 'created_at'],
        ];
    }
    
    protected function createModel()
    {
        return new OrderedItem(
            $this->context,
            $this->registry,
            $this->resource
        );
    }
}
