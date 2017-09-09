<?php


namespace Stockbase\Integration\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;
use Stockbase\Integration\Model\StockItemReserve;

/**
 * Class SalesOrderAfterPlaceObserverTest
 */
class SalesOrderAfterPlaceObserverTest extends TestCase
{
    /** @var StockbaseStockManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $stockbaseStockManagement;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->stockbaseStockManagement = $this->createMock(StockbaseStockManagement::class);
    }

    /**
     * testExecute
     */
    public function testExecute()
    {
        $item = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $item->method('getQuoteItemId')->willReturn(101);
        $item->method('getId')->willReturn(201);

        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->method('getAllItems')->willReturn([$item]);

        $observer = new \Magento\Framework\Event\Observer(
            [
                'event' => new \Magento\Framework\Event(
                    [
                        'order' => $order,
                    ]
                ),
            ]
        );

        $reserve = $this->createMock(StockItemReserve::class);
        $reserve->expects($this->once())->method('setOrderItemId')->with(201);
        $reserve->expects($this->once())->method('save');

        $this->stockbaseStockManagement->method('getReserveForQuoteItem')->with(101)->willReturn(
            [
                101 => $reserve,
            ]
        );

        $handler = new \Stockbase\Integration\Observer\SalesOrderAfterPlaceObserver($this->stockbaseStockManagement);
        $handler->execute($observer);
    }
}
