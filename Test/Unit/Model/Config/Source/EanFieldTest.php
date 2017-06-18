<?php


namespace Stockbase\Integration\Test\Unit\Model\Config\Source;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\Config\Source\EanField;

/**
 * Class EanFieldTest
 */
class EanFieldTest extends TestCase
{
    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var Collection|\PHPUnit_Framework_MockObject_MockObject */
    private $collection;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->collection = $this->createMock(Collection::class);
        
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->objectManager->method('get')->with(Collection::class)->willReturn($this->collection);
    }

    /**
     * testOptions
     */
    public function testOptions()
    {
        $this->collection->expects($this->once())->method('addFieldToFilter')
            ->with(Set::KEY_ENTITY_TYPE_ID, 4);
        
        $this->collection->expects($this->once())->method('load')
            ->willReturnSelf();
        
        $items = [];
        $item = $this->createMock(AttributeMetadataInterface::class);
        $item->method('getFrontendLabel')->willReturn('Test Attribute');
        $item->method('getAttributeCode')->willReturn('test');
        $items[] = $item;
        
        $this->collection->expects($this->once())->method('getItems')
            ->willReturn($items);
        
        $fieldSource = new EanField($this->objectManager);
        $result = $fieldSource->toOptionArray();
        
        $expectedResult = [
            ['label' => '', 'value' => ''],
            ['label' => 'Test Attribute', 'value' => 'test'],
        ];
        
        $this->assertEquals($expectedResult, $result);
    }
}
