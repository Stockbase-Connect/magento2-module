<?php


namespace Stockbase\Integration\Test\Unit\Model\Observer;

use Magento\Catalog\Model\Indexer\Product\Price\Processor as ProductPriceProcessor;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;
use Stockbase\Integration\Model\Observer\CancelOrderItemObserver;
use Stockbase\Integration\Model\StockItemReserve;

/**
 * Class CancelOrderItemObserverTest
 */
class CancelOrderItemObserverTest extends TestCase
{
    /** @var StockManagementInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $stockManagement;

    /** @var ProductPriceProcessor|\PHPUnit_Framework_MockObject_MockObject */
    private $priceIndexer;

    /** @var StockbaseStockManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $stockbaseStockManagement;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->stockManagement = $this->createMock(StockManagementInterface::class);
        $this->priceIndexer = $this->createMock(ProductPriceProcessor::class);
        $this->stockbaseStockManagement = $this->createMock(StockbaseStockManagement::class);
    }

    /**
     * testExecute
     * @dataProvider itemProvider
     *
     * @param mixed $qty
     * @param mixed $stockbaseQty
     * @param mixed $magentoQty
     */
    public function testExecute($qty, $stockbaseQty, $magentoQty)
    {
        $store = $this->createMock(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn(1);
        
        $item = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $item->method('getQtyOrdered')->willReturn($qty);
        $item->method('getQtyShipped')->willReturn(0);
        $item->method('getQtyInvoiced')->willReturn(0);
        $item->method('getQtyCanceled')->willReturn(0);
        $item->method('getId')->willReturn(101);
        $item->method('getProductId')->willReturn(201);
        $item->method('getQuoteItemId')->willReturn(301);
        $item->method('getStore')->willReturn($store);
        
        $observer = new \Magento\Framework\Event\Observer([
            'event' => new \Magento\Framework\Event([
                'item' => $item,
            ]),
        ]);
        
        $reserve = $this->createMock(StockItemReserve::class);
        $reserve->method('getAmount')->willReturn($stockbaseQty);
        $reserve->method('getMagentoStockAmount')->willReturn($magentoQty);

        $this->stockbaseStockManagement->method('getReserveForQuoteItem')->with(301)->willReturn([
            301 => $reserve,
        ]);

        $this->stockbaseStockManagement->expects($this->once())->method('releaseReserve')->with($reserve);
        $this->stockManagement->expects($this->once())->method('backItemQty')->with(201, $magentoQty, 1);
        $this->priceIndexer->expects($this->once())->method('reindexRow')->with(201);
        
        $handler = $this->createHandler();
        $handler->execute($observer);
    }

    /**
     * @return array
     */
    public function itemProvider()
    {
        return [
            [5, 3, 2],
            [10, 0, 10],
            [7, 7, 0],
        ];
    }
    
    protected function createHandler()
    {
        return new CancelOrderItemObserver(
            $this->stockManagement,
            $this->priceIndexer,
            $this->stockbaseStockManagement
        );
    }
}
