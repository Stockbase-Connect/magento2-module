<?php


namespace Stockbase\Integration\Test\Unit\Model\Inventory;

use Magento\CatalogInventory\Api\Data\StockItemExtensionInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\Inventory\CombinedStockbaseStockItem;

/**
 * Class CombinedStockbaseStockItemTest
 */
class CombinedStockbaseStockItemTest extends TestCase
{
    /** @var StockItemInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $magentoStockItem;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->magentoStockItem = $this->createMock(StockItemInterface::class);
    }

    /**
     * testGetQty
     * @dataProvider getQtyProvider
     *
     * @param mixed $magentoStockQty
     * @param mixed $stockbaseStockQty
     * @param mixed $expectedResult
     */
    public function testGetQty($magentoStockQty, $stockbaseStockQty, $expectedResult)
    {
        $this->magentoStockItem->expects($this->once())->method('getQty')->willReturn($magentoStockQty);

        $item = new CombinedStockbaseStockItem($this->magentoStockItem, $stockbaseStockQty);
        $this->assertEquals($expectedResult, $item->getQty());
    }

    /**
     * @return array
     */
    public function getQtyProvider()
    {
        return [
            [2, 3, 5],
            [0, 2, 2],
            [7, 0, 7],
            [0, 0, 0],
            [null, 1, 1],
        ];
    }

    /**
     * testSetQty
     */
    public function testSetQty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Can not set quantity on combined Stockbase stock item.');

        $item = new CombinedStockbaseStockItem($this->magentoStockItem, 0);
        $item->setQty(1);
    }

    /**
     * testMethodPassThrough
     * @dataProvider methodPassThroughProvider
     *
     * @param mixed $method
     * @param mixed $arg
     * @param mixed $returnSelf
     */
    public function testMethodPassThrough($method, $arg, $returnSelf)
    {
        $item = new CombinedStockbaseStockItem($this->magentoStockItem, 0);

        $methodMock = $this->magentoStockItem->expects($this->at(0))->method($method);
        if ($returnSelf) {
            $methodMock->willReturnSelf();
            $expectedResult = $item;
        } else {
            $methodMock->willReturn('TEST_RESULT');
            $expectedResult = 'TEST_RESULT';
        }

        if ($arg === null) {
            $methodMock->with();
            $result = $item->{$method}();
        } else {
            $methodMock->with($arg);
            $result = $item->{$method}($arg);
        }

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function methodPassThroughProvider()
    {
        return [
            ['getItemId', null, false],
            ['setItemId', 1111, true],
            ['getProductId', null, false],
            ['setProductId', 1111, true],
            ['getStockId', null, false],
            ['setStockId', 1111, true],
            ['getIsInStock', null, false],
            ['setIsInStock', 1111, true],
            ['getIsQtyDecimal', null, false],
            ['setIsQtyDecimal', 1111, true],
            ['getIsQtyDecimal', null, false],
            ['setIsQtyDecimal', 1111, true],
            ['getShowDefaultNotificationMessage', null, false],
            ['getUseConfigMinQty', null, false],
            ['setUseConfigMinQty', 1111, true],
            ['getMinQty', null, false],
            ['setMinQty', 1111, true],
            ['getUseConfigMinSaleQty', null, false],
            ['setUseConfigMinSaleQty', 1111, true],
            ['getMinSaleQty', null, false],
            ['setMinSaleQty', 1111, true],
            ['getUseConfigMaxSaleQty', null, false],
            ['setUseConfigMaxSaleQty', 1111, true],
            ['getMaxSaleQty', null, false],
            ['setMaxSaleQty', 1111, true],
            ['getUseConfigBackorders', null, false],
            ['setUseConfigBackorders', 1111, true],
            ['getBackorders', null, false],
            ['setBackorders', 1111, true],
            ['getUseConfigNotifyStockQty', null, false],
            ['setUseConfigNotifyStockQty', 1111, true],
            ['getNotifyStockQty', null, false],
            ['setNotifyStockQty', 1111, true],
            ['getUseConfigQtyIncrements', null, false],
            ['setUseConfigQtyIncrements', 1111, true],
            ['getQtyIncrements', null, false],
            ['setQtyIncrements', 1111, true],
            ['getUseConfigEnableQtyInc', null, false],
            ['setUseConfigEnableQtyInc', 1111, true],
            ['getEnableQtyIncrements', null, false],
            ['setEnableQtyIncrements', 1111, true],
            ['getUseConfigManageStock', null, false],
            ['setUseConfigManageStock', 1111, true],
            ['getManageStock', null, false],
            ['setManageStock', 1111, true],
            ['getLowStockDate', null, false],
            ['setLowStockDate', 1111, true],
            ['getIsDecimalDivided', null, false],
            ['setIsDecimalDivided', 1111, true],
            ['getStockStatusChangedAuto', null, false],
            ['setStockStatusChangedAuto', 1111, true],
            ['getExtensionAttributes', null, false],
            [
                'setExtensionAttributes',
                $this->getMockBuilder(StockItemExtensionInterface::class)->getMock(),
                true,
            ],
        ];
    }

    /**
     * testMagicCallPassThrough
     */
    public function testMagicCallPassThrough()
    {
        $magentoStockItem = $this->getMockBuilder(\Magento\CatalogInventory\Model\Stock\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['nonExistentMethod1', 'nonExistentMethod2'])
            ->getMock();

        $magentoStockItem->expects($this->at(0))->method('nonExistentMethod1')->with();
        $magentoStockItem->expects($this->at(1))->method('nonExistentMethod2')->with(1, 2, 3);

        $item = new CombinedStockbaseStockItem($magentoStockItem, 0);

        $item->{'nonExistentMethod1'}();
        $item->{'nonExistentMethod2'}(1, 2, 3);
    }
}
