<?php


namespace Stockbase\Integration\Test\Unit\Model\Observer;

use Magento\CatalogInventory\Observer\ProductQty;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as IndexStockProcessor;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceProcessor;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;
use Stockbase\Integration\Model\Observer\RevertQuoteInventoryObserver;
use Stockbase\Integration\Model\StockItemReserve;

/**
 * Class RevertQuoteInventoryObserverTest
 */
class RevertQuoteInventoryObserverTest extends TestCase
{
    const TEST_WEBSITE_ID = 0xdeadbeef;
    
    /** @var ProductQty|\PHPUnit_Framework_MockObject_MockObject */
    private $productQty;

    /** @var StockManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $stockManagement;

    /** @var IndexStockProcessor|\PHPUnit_Framework_MockObject_MockObject */
    private $stockIndexerProcessor;

    /** @var ProductPriceProcessor|\PHPUnit_Framework_MockObject_MockObject */
    private $priceIndexer;

    /** @var StockbaseStockManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $stockbaseStockManagement;

    /** @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject */
    private $quote;

    /** @var \Magento\Framework\Event\Observer */
    private $observer;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->productQty = $this->createMock(ProductQty::class);
        
        $this->stockManagement = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockManagement::class)
            ->disableOriginalConstructor()
            ->setMethods(['revertProductsSale'])
            ->getMock();
        
        $this->stockIndexerProcessor = $this->createMock(IndexStockProcessor::class);
        
        $this->priceIndexer = $this->createMock(ProductPriceProcessor::class);
        
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

        $store = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $store->method('getWebsiteId')->willReturn(self::TEST_WEBSITE_ID);

        $this->quote->method('getStore')->willReturn($store);
        
        $this->observer = new \Magento\Framework\Event\Observer([
            'event' => new \Magento\Framework\Event([
                'quote' => $this->quote,
            ]),
        ]);
    }

    /**
     * testExecute
     * @dataProvider itemsProvider
     */
    public function testExecute()
    {
        $itemPrototypes = func_get_args();
        
        $items = [];
        $magentoReserve = [];
        foreach ($itemPrototypes as $index => $itemPrototype) {
            $item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
                ->disableOriginalConstructor()
                ->setMethods(['getProductId', 'getChildrenItems', 'getTotalQty', 'getId'])
                ->getMock();

            $item->method('getId')->willReturn($itemPrototype['id']);
            $item->method('getProductId')->willReturn($itemPrototype['productId']);
            $item->method('getChildrenItems')->willReturn([]);
            $item->method('getTotalQty')->willReturn($itemPrototype['requestedQty']);

            $items[$index] = $item;
            $magentoReserve[$itemPrototype['productId']] = $itemPrototype['magentoReserve'];

            $reserveItem = $this->createMock(StockItemReserve::class);
            $reserveItem->method('getEan')->willReturn($itemPrototype['productId']);
            $reserveItem->method('getAmount')->willReturn($itemPrototype['stockbaseReserve']);
            $reserveItem->method('getMagentoStockAmount')->willReturn($itemPrototype['magentoReserve']);

            $this->stockbaseStockManagement->expects(new MethodInvokedAtIndex($index))->method('getReserveForQuoteItem')
                ->with($itemPrototype['id'])
                ->willReturn([$reserveItem]);

            $this->stockbaseStockManagement->expects(new MethodInvokedAtIndex($index))->method('releaseReserve')
                ->with($reserveItem);
        }
        
        $this->quote->method('getAllItems')->willReturn($items);
        
        $this->stockManagement->expects($this->once())->method('revertProductsSale')
            ->with($magentoReserve, self::TEST_WEBSITE_ID);
        
        $productIds = array_keys($magentoReserve);
        if (!empty($productIds)) {
            $this->stockIndexerProcessor->expects($this->once())->method('reindexList')->with($productIds);
            $this->priceIndexer->expects($this->once())->method('reindexList')->with($productIds);
        } else {
            $this->stockIndexerProcessor->expects($this->never())->method('reindexList');
            $this->priceIndexer->expects($this->never())->method('reindexList');
        }
        
        $this->quote->expects($this->once())->method('setInventoryProcessed')->with(false);

        $handler = $this->createHandler();
        $handler->execute($this->observer);
    }

    /**
     * @return array
     */
    public function itemsProvider()
    {
        return [
            [
                [
                    'id' => 201,
                    'productId' => 101,
                    'requestedQty' => 5,
                    'magentoReserve' => 3,
                    'stockbaseReserve' => 2,
                ],
                [
                    'id' => 202,
                    'productId' => 102,
                    'requestedQty' => 3,
                    'magentoReserve' => 0,
                    'stockbaseReserve' => 3,
                ],
            ],
            [
                [
                    'id' => 201,
                    'productId' => 101,
                    'requestedQty' => 6,
                    'magentoReserve' => 6,
                    'stockbaseReserve' => 0,
                ],
            ],
            [],
        ];
    }

    protected function createHandler()
    {
        return new RevertQuoteInventoryObserver(
            $this->productQty,
            $this->stockManagement,
            $this->stockIndexerProcessor,
            $this->priceIndexer,
            $this->stockbaseStockManagement
        );
    }
}
