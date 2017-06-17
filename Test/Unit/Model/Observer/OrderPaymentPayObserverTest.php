<?php


namespace Stockbase\Integration\Test\Unit\Model\Observer;

use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\Model\Inventory\StockbaseStockManagement;
use Stockbase\Integration\Model\Observer\OrderPaymentPayObserver;
use Stockbase\Integration\Model\StockItemReserve;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClient;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClientFactory;
use Stockbase\Integration\Model\OrderedItem as StockbaseOrderedItem;

/**
 * Class OrderPaymentPayObserverTest
 */
class OrderPaymentPayObserverTest extends TestCase
{
    /** @var StockbaseConfiguration|\PHPUnit_Framework_MockObject_MockObject */
    private $stockbaseConfiguration;

    /** @var StockbaseStockManagement|\PHPUnit_Framework_MockObject_MockObject */
    private $stockbaseStockManagement;

    /** @var StockbaseClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $stockbaseClientFactory;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManager;

    /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject */
    private $observer;

    /** @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject */
    private $order;

    /** @var \Magento\Sales\Api\Data\InvoiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $invoice;

    /** @var StockbaseClient|\PHPUnit_Framework_MockObject_MockObject */
    private $stockbaseClient;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->stockbaseConfiguration = $this->createMock(StockbaseConfiguration::class);
        
        $this->stockbaseStockManagement = $this->createMock(StockbaseStockManagement::class);

        $this->stockbaseClientFactory = $this->createMock(StockbaseClientFactory::class);
        
        $this->stockbaseClient = $this->createMock(StockbaseClient::class);
        
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        
        $this->order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllItems', 'getId', 'save', 'addStatusHistoryComment'])
            ->getMock();
        
        $this->invoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder', 'getOrderId'])
            ->getMock();
        
        $this->observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->setMethods(['getData', 'getInvoice'])
            ->getMock();
    }

    /**
     * testDisabled
     */
    public function testDisabled()
    {
        $this->stockbaseConfiguration->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(false);

        $this->observer->expects($this->never())->method('getData');
        $this->observer->expects($this->never())->method('getInvoice');
        
        $handler = $this->createHandler();
        $this->assertEquals($handler, $handler->execute($this->observer));
    }

    /**
     * testNotReserved
     */
    public function testNotReserved()
    {
        $this->stockbaseConfiguration->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(true);

        $this->observer->method('getData')->with('invoice')->willReturn($this->invoice);
        $this->invoice->method('getOrder')->willReturn($this->order);
        
        $this->order->method('getAllItems')->willReturn($this->createOrderItems([101, 102]));

        $this->stockbaseStockManagement->expects($this->once())
            ->method('getReserveForQuoteItem')
            ->with([101, 102])
            ->willReturn([]);

        $this->stockbaseClientFactory->expects($this->never())->method('create');

        $handler = $this->createHandler();
        $this->assertEquals($handler, $handler->execute($this->observer));
    }

    /**
     * testStockbaseOrderCreation
     */
    public function testStockbaseOrderCreation()
    {
        $this->stockbaseConfiguration->expects($this->once())
            ->method('isModuleEnabled')
            ->willReturn(true);

        $this->observer->method('getData')->with('invoice')->willReturn($this->invoice);
        $this->invoice->method('getOrder')->willReturn($this->order);
        
        $this->order->method('getAllItems')->willReturn($this->createOrderItems([101]));

        $reservedStockbaseItem = $this->createMock(StockItemReserve::class);
        $reservedStockbaseItem->method('getEan')->willReturn('12345');
        $reservedStockbaseItem->method('getAmount')->willReturn(5.0);
        $reservedStockbaseItem->method('getOrderItemId')->willReturn(12345);
        $reservedStockbaseItem->method('getProductId')->willReturn(1234);
        
        $reservedStockbaseItems = [$reservedStockbaseItem];

        $this->stockbaseStockManagement->expects($this->once())
            ->method('getReserveForQuoteItem')
            ->with([101])
            ->willReturn($reservedStockbaseItems);

        $this->stockbaseClientFactory->method('create')->willReturn($this->stockbaseClient);

        $this->stockbaseClient->expects($this->once())->method('createOrder')
            ->with($this->order, $reservedStockbaseItems);

        $stockbaseOrderedItem = $this->createMock(StockbaseOrderedItem::class);
        $stockbaseOrderedItem->expects($this->any())->method('setOrderItemId')->with(12345);
        $stockbaseOrderedItem->expects($this->any())->method('setEan')->with('12345');
        $stockbaseOrderedItem->expects($this->any())->method('setAmount')->with(5.0);
        $stockbaseOrderedItem->expects($this->once())->method('save');
        
        $this->objectManager->expects($this->once())->method('create')->with(StockbaseOrderedItem::class)
            ->willReturn($stockbaseOrderedItem);

        $this->stockbaseStockManagement->expects($this->once())->method('updateStockAmount')
            ->with('12345', 5.0, '-');

        $this->stockbaseStockManagement->expects($this->once())->method('releaseReserve')
            ->with($reservedStockbaseItem);

        $this->order->expects($this->exactly(2))->method('addStatusHistoryComment')
            ->withConsecutive(
                [$this->matchesRegularExpression('/has been ordered from Stockbase/'), $this->anything()],
                [$this->matchesRegularExpression('/Local Stockbase stock index for item with/'), $this->anything()]
            );

        $handler = $this->createHandler();
        $this->assertEquals($handler, $handler->execute($this->observer));
    }
    
    protected function createHandler()
    {
        return new OrderPaymentPayObserver(
            $this->stockbaseConfiguration,
            $this->stockbaseStockManagement,
            $this->stockbaseClientFactory,
            $this->objectManager
        );
    }
    
    protected function createOrderItems(array $quoteItemIds)
    {
        $items = [];
        foreach ($quoteItemIds as $quoteItemId) {
            $item = $this->createMock(\Magento\Sales\Api\Data\OrderItemInterface::class);
            $item->method('getQuoteItemId')->willReturn($quoteItemId);
            $items[] = $item;
        }
        
        return $items;
    }
}
