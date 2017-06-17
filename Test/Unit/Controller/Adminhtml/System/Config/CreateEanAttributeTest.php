<?php


namespace Stockbase\Integration\Test\Unit\Controller\Adminhtml\System\Config;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Controller\Adminhtml\System\Config\CreateEanAttribute;

/**
 * Class CreateEanAttributeTest
 */
class CreateEanAttributeTest extends TestCase
{
    const TEST_PRODUCT_TYPE_ID = 0xdeadbeef;
    
    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $request;
    
    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $response;
    
    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $resultFactory;

    /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject */
    private $jsonResult;

    /** @var AttributeFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeFactory;

    /** @var AttributeManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeManagement;

    /** @var AttributeSetCollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeSetCollectionFactory;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var \Magento\Eav\Model\Entity|\PHPUnit_Framework_MockObject_MockObject */
    private $eavEntity;

    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    private $attribute;

    /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeSetCollection;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        
        $this->attributeFactory = $this->getMockBuilder(AttributeFactory::class)->setMethods(['create'])->getMock();
        $this->attributeFactory->method('create')
            ->willReturn($this->attribute);
        
        $this->attributeManagement = $this->createMock(AttributeManagementInterface::class);
        
        $this->eavEntity = $this->createMock(\Magento\Eav\Model\Entity::class);
        $this->eavEntity
            ->method('setType')
            ->willReturnSelf();
        $this->eavEntity
            ->method('getTypeId')
            ->willReturn(self::TEST_PRODUCT_TYPE_ID);


        $this->attributeSetCollection = $this->createMock(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::class);
        
        $this->attributeSetCollectionFactory = $this->getMockBuilder(AttributeSetCollectionFactory::class)
            ->setMethods(['create'])->getMock();

        $this->attributeSetCollectionFactory
            ->method('create')
            ->willReturn($this->attributeSetCollection);

        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->objectManager
            ->method('create')
            ->with(\Magento\Eav\Model\Entity::class)
            ->willReturn($this->eavEntity);

        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->response = $this->createMock(\Magento\Framework\App\ResponseInterface::class);

        $this->jsonResult = $this->createMock(\Magento\Framework\Controller\Result\Json::class);

        $this->resultFactory = $this->createMock(\Magento\Framework\Controller\ResultFactory::class);
        $this->resultFactory->method('create')->willReturn($this->jsonResult);

        $this->context = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);
        $this->context->method('getObjectManager')->willReturn($this->objectManager);
    }

    /**
     * testInvalidRequest
     */
    public function testInvalidRequest()
    {
        $this->jsonResult->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST);
        
        $controller = $this->createController();
        $this->assertEquals($this->jsonResult, $controller->execute());
    }

    /**
     * testLoadExistingAttribute
     */
    public function testLoadExistingAttribute()
    {
        $this->request->expects($this->once())->method('isPost')->willReturn(true);
        
        $this->attribute->expects($this->once())->method('loadByCode')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, 'ean');
        $this->attribute->expects($this->once())->method('isEmpty')->willReturn(false);
        $this->attribute->expects($this->any())->method('getDefaultFrontendLabel')->willReturn('TEST_LABEL');
        $this->attribute->expects($this->any())->method('getAttributeCode')->willReturn('TEST_VALUE');

        $this->attribute->expects($this->never())->method('save');
        
        $this->jsonResult->expects($this->once())
            ->method('setData')
            ->with([
                'eanField' => [
                    'label' => 'TEST_LABEL',
                    'value' => 'TEST_VALUE',
                ],
            ]);
        
        $controller = $this->createController();
        $this->assertEquals($this->jsonResult, $controller->execute());
    }

    /**
     * testCreateNewAttribute
     */
    public function testCreateNewAttribute()
    {
        $this->request->expects($this->once())->method('isPost')->willReturn(true);

        $this->attribute->expects($this->once())->method('loadByCode')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, 'ean');
        $this->attribute->expects($this->once())->method('isEmpty')->willReturn(true);

        $this->attribute->expects($this->once())->method('setEntityTypeId')->with(self::TEST_PRODUCT_TYPE_ID);
        $this->attribute->expects($this->once())->method('setAttributeCode')->with('ean');
        $this->attribute->expects($this->once())->method('setDefaultFrontendLabel')->with('EAN');
        $this->attribute->expects($this->any())->method('getDefaultFrontendLabel')->willReturn('EAN');
        $this->attribute->expects($this->any())->method('getAttributeCode')->willReturn('ean');
        $this->attribute->expects($this->once())->method('save');
        
        $this->attributeSetCollection->expects($this->once())
            ->method('setEntityTypeFilter')
            ->with(self::TEST_PRODUCT_TYPE_ID);
        
        $attributeSets = [];
        for ($i = 0; $i < 2; $i++) {
            $attributeSet = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Set::class)
                ->disableOriginalConstructor()
                ->setMethods(['getAttributeSetId', 'getDefaultGroupId'])
                ->getMock();
            
            $attributeSet->expects($this->any())->method('getAttributeSetId')->willReturn($i*100+1);
            $attributeSet->expects($this->any())->method('getDefaultGroupId')->willReturn($i*100+2);
            
            $attributeSets[$i] = $attributeSet;
        }
        
        $this->attributeSetCollection->expects($this->once())->method('getItems')->willReturn($attributeSets);

        foreach ($attributeSets as $index => $attributeSet) {
            $this->attributeManagement->expects(new MethodInvokedAtIndex($index))->method('assign')
                ->with(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $attributeSet->getAttributeSetId(),
                    $attributeSet->getDefaultGroupId(),
                    'ean',
                    0
                );
        }

        $this->jsonResult->expects($this->once())
            ->method('setData')
            ->with([
                'eanField' => [
                    'label' => 'EAN',
                    'value' => 'ean',
                ],
            ]);

        $controller = $this->createController();
        $this->assertEquals($this->jsonResult, $controller->execute());
    }

    protected function createController()
    {
        return new CreateEanAttribute(
            $this->context,
            $this->attributeFactory,
            $this->attributeManagement,
            $this->attributeSetCollectionFactory
        );
    }
}
