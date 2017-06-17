<?php


namespace Stockbase\Integration\Test\Unit\Model\Observer;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\CatalogInventory\Observer\ProductQty;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;
use Stockbase\Integration\Model\Observer\SubtractQuoteInventoryObserver;
use Stockbase\Integration\Model\StockItemReserve;

/**
 * Class SubtractQuoteInventoryObserverTest
 */
class SubtractQuoteInventoryObserverTest extends TestCase
{
    const TEST_WEBSITE_ID = 0xdeadbeef;
    
    /** @var StockManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $stockManagement;

    /** @var ProductQty|\PHPUnit_Framework_MockObject_MockObject */
    private $productQty;

    /** @var ItemsForReindex|\PHPUnit_Framework_MockObject_MockObject */
    private $itemsForReindex;

    /** @var StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $stockRegistry;

    /** @var StockbaseStockManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $stockbaseStockManagement;

    /** @var EventObserver */
    private $observer;

    /** @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject */
    private $quote;

    /** @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject */
    private $order;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->stockManagement = $this->createMock(\Magento\CatalogInventory\Model\StockManagement::class);
        $this->productQty = $this->createMock(ProductQty::class);
        $this->itemsForReindex = $this->createMock(ItemsForReindex::class);
        $this->stockRegistry = $this->createMock(StockRegistryInterface::class);
        $this->stockbaseStockManagement = $this->createMock(StockbaseStockManagement::class);

        $this->quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getInventoryProcessed',
                'setInventoryProcessed',
                'getAllItems',
                'getStore',
            ])
            ->getMock();
        
        $this->order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['addStatusHistoryComment'])
            ->getMock();
        
        $store = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $store->method('getWebsiteId')->willReturn(self::TEST_WEBSITE_ID);
        
        $this->quote->method('getStore')->willReturn($store);
        
        
        $this->observer = new \Magento\Framework\Event\Observer([
            'event' => new \Magento\Framework\Event([
                'quote' => $this->quote,
                'order' => $this->order,
            ]),
        ]);
    }

    /**
     * testInventoryProcessed
     */
    public function testInventoryProcessed()
    {
        $this->quote->method('getInventoryProcessed')->willReturn(true);
        
        $this->quote->expects($this->never())->method('setInventoryProcessed');
        
        $handler = $this->createHandler();
        $this->assertEquals($handler, $handler->execute($this->observer));
    }

    /**
     * testEmptyItems
     */
    public function testEmptyItems()
    {
        $this->quote->method('getInventoryProcessed')->willReturn(false);
        $this->quote->method('getAllItems')->willReturn([]);

        $this->stockManagement->expects($this->once())
            ->method('registerProductsSale')
            ->with([], self::TEST_WEBSITE_ID)
            ->willReturn([]);

        $this->itemsForReindex->expects($this->once())->method('setItems')->with([]);
        $this->quote->expects($this->once())->method('setInventoryProcessed')->with(true);
        
        $handler = $this->createHandler();
        $this->assertEquals($handler, $handler->execute($this->observer));
    }

    /**
     * testItems
     * @dataProvider itemsProvider
     */
    public function testItems()
    {
        $itemPrototypes = func_get_args();

        $items = [];
        $magentoReserve = [];
        $stockbaseAmounts = [];
        foreach ($itemPrototypes as $index => $itemPrototype) {
            $item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
                ->disableOriginalConstructor()
                ->setMethods(['getProductId', 'getChildrenItems', 'getTotalQty'])
                ->getMock();

            $item->method('getProductId')->willReturn($itemPrototype['productId']);
            $item->method('getChildrenItems')->willReturn([]);
            $item->method('getTotalQty')->willReturn($itemPrototype['requestedQty']);

            $items[$index] = $item;
            $magentoReserve[$itemPrototype['productId']] = $itemPrototype['magentoReserve'];
            $stockbaseAmounts[$itemPrototype['productId']] = $itemPrototype['stockbaseQty'];

            $stockItem = $this->createMock(\Magento\CatalogInventory\Api\Data\StockItemInterface::class);
            $stockItem->method('getQty')->willReturn($itemPrototype['magentoQty']);
            
            $this->stockRegistry->expects(new MethodInvokedAtIndex($index))->method('getStockItem')
                ->with($itemPrototype['productId'])
                ->willReturn($stockItem);
            
            if (!empty($itemPrototype['qtyException'])) {
                $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
                $this->expectExceptionMessageRegExp('/Not all of your products are available in the requested quantity\./');
            } else {
                $reserveItem = $this->createMock(StockItemReserve::class);
                $reserveItem->method('getEan')->willReturn($itemPrototype['productId']);
                $reserveItem->method('getAmount')->willReturn($itemPrototype['stockbaseReserve']);

                $this->stockbaseStockManagement->expects(new MethodInvokedAtIndex($index))->method('createReserve')
                    ->with($item, $itemPrototype['stockbaseReserve'], $itemPrototype['magentoReserve'])
                    ->willReturn($reserveItem);
            }
        }
        
        
        $this->stockbaseStockManagement->method('isStockbaseProduct')->willReturn(true);

        $this->stockbaseStockManagement->method('getStockbaseStockAmount')
            ->willReturnCallback(function ($productId) use ($stockbaseAmounts) {
                return $stockbaseAmounts[$productId];
            });
        
        $this->quote->method('getInventoryProcessed')->willReturn(false);
        $this->quote->method('getAllItems')->willReturn($items);

        if (!$this->getExpectedException()) {
            $this->stockManagement->expects($this->once())
                ->method('registerProductsSale')
                ->with($magentoReserve, self::TEST_WEBSITE_ID)
                ->willReturn([]);
            
            $this->itemsForReindex->expects($this->once())->method('setItems')->with([]);

            $this->quote->expects($this->once())->method('setInventoryProcessed')->with(true);
        }

        $handler = $this->createHandler();
        $this->assertEquals($handler, $handler->execute($this->observer));
    }

    /**
     * itemsProvider
     * @return array
     */
    public function itemsProvider()
    {
        return [
            [
                [
                    'productId' => 101,
                    'magentoQty' => 3,
                    'stockbaseQty' => 2,
                    'requestedQty' => 5,
                    'magentoReserve' => 3,
                    'stockbaseReserve' => 2,
                ],
                [
                    'productId' => 102,
                    'magentoQty' => 0,
                    'stockbaseQty' => 5,
                    'requestedQty' => 3,
                    'magentoReserve' => 0,
                    'stockbaseReserve' => 3,
                ],
            ],
            [
                [
                    'productId' => 101,
                    'magentoQty' => 3,
                    'stockbaseQty' => 2,
                    'requestedQty' => 6,
                    'magentoReserve' => 0,
                    'stockbaseReserve' => 0,
                    'qtyException' => true,
                ],
            ],
        ];
    }

    protected function createHandler()
    {
        return new SubtractQuoteInventoryObserver(
            $this->stockManagement,
            $this->productQty,
            $this->itemsForReindex,
            $this->stockRegistry,
            $this->stockbaseStockManagement
        );
    }
}
