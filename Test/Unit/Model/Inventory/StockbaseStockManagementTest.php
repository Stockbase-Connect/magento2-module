<?php


namespace Stockbase\Integration\Test\Unit\Model\Inventory;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;
use Stockbase\Integration\Model\ResourceModel\StockItem as StockItemResource;
use Stockbase\Integration\Model\ResourceModel\StockItemReserve\Collection as StockItemReserveCollection;
use Stockbase\Integration\Model\StockItemReserve;

/**
 * Class StockbaseStockManagementTest
 */
class StockbaseStockManagementTest extends TestCase
{

    /** @var StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $stockRegistry;

    /** @var StockbaseConfiguration|\PHPUnit_Framework_MockObject_MockObject */
    private $config;

    /** @var ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $productFactory;

    /** @var StockItemResource|\PHPUnit_Framework_MockObject_MockObject */
    private $stockItemResource;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var \Magento\CatalogInventory\Model\Stock\Item|\PHPUnit_Framework_MockObject_MockObject */
    private $stockItem;

    /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject */
    private $product;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->stockItem = $this->createMock(\Magento\CatalogInventory\Model\Stock\Item::class);
        
        $this->stockRegistry = $this->createMock(StockRegistryInterface::class);
        
        $this->config = $this->createMock(StockbaseConfiguration::class);
        
        $this->product = $this->createMock(\Magento\Catalog\Model\Product::class);
        
        $this->productFactory = $this->getMockBuilder('\Magento\Catalog\Model\ProductFactory')
            ->setMethods(['create'])
            ->getMock();
        $this->productFactory->method('create')->willReturn($this->product);
        
        $this->stockItemResource = $this->createMock(StockItemResource::class);
        
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
    }

    /**
     * testGetStockbaseStockAmount
     */
    public function testGetStockbaseStockAmount()
    {
        $this->configureStockbaseEan(101, '12345');

        $this->stockItemResource->expects($this->once())->method('getNotReservedStockAmount')
            ->with('12345')
            ->willReturn(5.0);
        
        $model = $this->createModel();
        $result = $model->getStockbaseStockAmount(101);
        
        $this->assertEquals(5.0, $result);
    }

    /**
     * testGetStockbaseEan
     */
    public function testGetStockbaseEan()
    {
        $this->configureStockbaseEan(101, '12345');
        
        $model = $this->createModel();
        $result = $model->getStockbaseEan(101);
        
        $this->assertEquals('12345', $result);
    }

    /**
     * testIsStockbaseProduct
     */
    public function testIsStockbaseProduct()
    {
        $this->configureStockbaseEan(101, '12345');
        
        $model = $this->createModel();
        $result = $model->isStockbaseProduct(101);
        
        $this->assertEquals(true, $result);
    }

    /**
     * testUpdateStockAmount
     * @dataProvider updateStockAmountProvider
     *
     * @param mixed $ean
     * @param mixed $amount
     * @param mixed $operation
     */
    public function testUpdateStockAmount($ean, $amount, $operation)
    {
        $this->stockItemResource->expects($this->once())->method('updateStockAmount')
            ->with($ean, $amount, $operation);
        
        $model = $this->createModel();
        $model->updateStockAmount($ean, $amount, $operation);
    }

    /**
     * @return array
     */
    public function updateStockAmountProvider()
    {
        return [
            ['101', 5, '+'],
            ['102', 7, '-'],
        ];
    }

    /**
     * testCreateReserve
     */
    public function testCreateReserve()
    {
        $this->configureStockbaseEan(101, '12345');


        $quoteItem = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductId', 'getId'])
            ->getMock();
        $quoteItem->method('getProductId')->willReturn(101);
        $quoteItem->method('getId')->willReturn(201);

        $stockItemReserve = $this->createMock(StockItemReserve::class);
        
        $this->objectManager->expects($this->once())->method('create')->with(StockItemReserve::class)
            ->willReturn($stockItemReserve);

        $stockItemReserve->expects($this->at(0))->method('setData')->willReturnCallback(function ($data) {
            $this->assertArrayHasKey('ean', $data);
            $this->assertArrayHasKey('amount', $data);
            $this->assertArrayHasKey('magento_stock_amount', $data);
            $this->assertArrayHasKey('quote_item_id', $data);
            $this->assertArrayHasKey('product_id', $data);
            $this->assertArrayHasKey('created_at', $data);
            
            $this->assertEquals('12345', $data['ean']);
            $this->assertEquals(5, $data['amount']);
            $this->assertEquals(7, $data['magento_stock_amount']);
            $this->assertEquals(201, $data['quote_item_id']);
            $this->assertEquals(101, $data['product_id']);
            $this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data['created_at']);
        });
        $stockItemReserve->expects($this->at(1))->method('save');
        
        $model = $this->createModel();
        $result = $model->createReserve($quoteItem, 5, 7);
        
        $this->assertEquals($stockItemReserve, $result);
    }

    /**
     * testReleaseReserve
     */
    public function testReleaseReserve()
    {
        $reserve = $this->createMock(StockItemReserve::class);
        $reserve->expects($this->once())->method('delete');
        
        $model = $this->createModel();
        $model->releaseReserve($reserve);
    }

    /**
     * testGetReserveForProduct
     */
    public function testGetReserveForProduct()
    {
        $reserveCollection = $this->createMock(StockItemReserveCollection::class);
        
        $this->objectManager->expects($this->once())->method('create')
            ->with(StockItemReserveCollection::class)
            ->willReturn($reserveCollection);

        $reserve = $this->createMock(StockItemReserve::class);
        
        $reserveCollection->expects($this->any())->method('addFieldToFilter')
            ->with('product_id', ['in' => [101]]);
        
        $reserveCollection->expects($this->once())->method('getItems')
            ->willReturn([$reserve]);
        
        $model = $this->createModel();
        $result = $model->getReserveForProduct(101);
        
        $this->assertEquals([$reserve], $result);
    }

    /**
     * testGetReserveForQuoteItem
     */
    public function testGetReserveForQuoteItem()
    {
        $reserveCollection = $this->createMock(StockItemReserveCollection::class);
        
        $this->objectManager->expects($this->once())->method('create')
            ->with(StockItemReserveCollection::class)
            ->willReturn($reserveCollection);

        $reserve = $this->createMock(StockItemReserve::class);
        
        $reserveCollection->expects($this->any())->method('addFieldToFilter')
            ->with('quote_item_id', ['in' => [101]]);
        
        $reserveCollection->expects($this->once())->method('getItems')
            ->willReturn([$reserve]);
        
        $model = $this->createModel();
        $result = $model->getReserveForQuoteItem(101);
            
        $this->assertEquals([$reserve], $result);
    }
    
    protected function createModel()
    {
        return new StockbaseStockManagement(
            $this->stockRegistry,
            $this->config,
            $this->productFactory,
            $this->stockItemResource,
            $this->objectManager
        );
    }
    
    protected function configureStockbaseEan($productId, $ean)
    {
        $this->stockRegistry->method('getStockItem')->with($productId)->willReturn($this->stockItem);

        $this->config->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->config->expects($this->once())->method('getEanFieldName')->willReturn('ean');
        $this->stockItem->expects($this->once())->method('getManageStock')->willReturn(true);
        $this->stockItem->expects($this->once())->method('getBackorders')
            ->willReturn(\Magento\CatalogInventory\Model\Stock::BACKORDERS_NO);

        $this->product->expects($this->once())->method('load')->with($productId);
        $this->product->expects(new MethodInvokedAtIndex(0))->method('getData')->with('ean')->willReturn($ean);
        $this->product->expects(new MethodInvokedAtIndex(1))->method('getData')->with('stockbase_product')
            ->willReturn(true);
    }
}
