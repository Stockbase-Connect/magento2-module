<?php


namespace Stockbase\Integration\Test\Unit\Model\ResourceModel;

use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\ResourceModel\StockItem;

/**
 * Class StockItemTest
 */
class StockItemTest extends TestCase
{
    const TEST_PREFIX = 'TEST_';
    
    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    private $resources;

    /** @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $connection;

    /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject */
    private $select;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->select = $this->createMock(\Magento\Framework\DB\Select::class);
        
        $this->connection = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $this->connection->method('select')->willReturn($this->select);
        
        $this->resources = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->resources->method('getConnection')->willReturn($this->connection);
        $this->resources->method('getTableName')
            ->willReturnCallback(function ($table, $connectionName) {
                return self::TEST_PREFIX.$table;
            });
        
        $this->context = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $this->context->method('getResources')->willReturn($this->resources);
    }

    /**
     * testGetNotReservedStockAmount
     * @dataProvider getNotReservedStockAmountProvider
     *
     * @param mixed $request
     * @param array $data
     * @param mixed $expectedResult
     */
    public function testGetNotReservedStockAmount($request, array $data, $expectedResult)
    {
        $this->select->expects($this->once())->method('from')
            ->with(
                ['s' => self::TEST_PREFIX.'stockbase_stock'],
                ['s.ean', 'amount' => new \Zend_Db_Expr('IF(s.noos = 1, 1000000000, s.amount - COALESCE(SUM(r.amount), 0))')]
            )
            ->willReturnSelf();
        
        $this->select->expects($this->once())->method('joinLeft')
            ->with(
                ['r' => self::TEST_PREFIX.'stockbase_stock_reserve'],
                'r.ean = s.ean',
                null
            )
            ->willReturnSelf();
        
        $this->select->expects($this->once())->method('where')
            ->with('s.ean in (?)', $request)
            ->willReturnSelf();
        
        $this->select->expects($this->once())->method('group')
            ->with('s.ean')
            ->willReturnSelf();
        
        $this->connection->expects($this->once())->method('fetchPairs')
            ->with($this->select)
            ->willReturn($data);
        
        $resourceModel = $this->createResourceModel();
        
        $result = $resourceModel->getNotReservedStockAmount($request);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getNotReservedStockAmountProvider()
    {
        return [
            [111, [111 => 2], 2],
            [[101, 102], [101 => 5, 102 => 0], [101 => 5, 102 => 0]],
        ];
    }

    /**
     * testUpdateStockAmount
     * @dataProvider updateStockAmountProvider
     *
     * @param mixed $ean
     * @param mixed $amount
     * @param mixed $operation
     * @param mixed $amountExpr
     * @param mixed $numRows
     * @param mixed $expectedResult
     */
    public function testUpdateStockAmount($ean, $amount, $operation, $amountExpr, $numRows, $expectedResult)
    {
        $this->connection->expects($this->once())->method('update')
            ->with(
                self::TEST_PREFIX.'stockbase_stock',
                ['amount' => new \Zend_Db_Expr($amountExpr)],
                ['ean = ?' => $ean]
            )
            ->willReturn($numRows);
        
        $resourceModel = $this->createResourceModel();

        $result = $resourceModel->updateStockAmount($ean, $amount, $operation);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function updateStockAmountProvider()
    {
        return [
            [101, 5, '+', 'amount + 5', 1, true],
            [102, 2, '-', 'amount - 2', 1, true],
            [103, 2, null, 'amount + 2', 1, true],
            [104, '2.5', '-', 'amount - 2.5', 0, false],
        ];
    }

    /**
     * testGetLastModifiedItemDate
     * @dataProvider getLastModifiedItemDateProvider
     *
     * @param mixed $data
     * @param mixed $expectedResult
     */
    public function testGetLastModifiedItemDate($data, $expectedResult)
    {
        $this->select->expects($this->once())->method('from')
            ->with(self::TEST_PREFIX.'stockbase_stock', 'timestamp')
            ->willReturnSelf();
        
        $this->select->expects($this->once())->method('order')
            ->with('timestamp DESC')
            ->willReturnSelf();
        
        $this->select->expects($this->once())->method('limit')
            ->with(1)
            ->willReturnSelf();

        $this->connection->expects($this->once())->method('fetchCol')
            ->with($this->select)
            ->willReturn($data);

        $resourceModel = $this->createResourceModel();

        $result = $resourceModel->getLastModifiedItemDate();
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getLastModifiedItemDateProvider()
    {
        return [
            [
                [],
                null,
            ],
            [
                ['2001-02-03'],
                new \DateTime('2001-02-03'),
            ],
            [
                ['2001-02-03', '2001-03-04'],
                new \DateTime('2001-02-03'),
            ],
        ];
    }

    /**
     * testUpdateFromStockObject
     */
    public function testUpdateFromStockObject()
    {
        $item = new \stdClass();
        $item->{'EAN'} = '12345';
        $item->{'Amount'} = 5;
        $item->{'NOOS'} = false;
        $item->{'Timestamp'} = 1497797012;
        
        $group = new \stdClass();
        $group->{'Brand'} = 'TEST_BRAND';
        $group->{'Code'} = 'TEST_CODE';
        $group->{'SupplierCode'} = 'TEST_SUPPLIER_CODE';
        $group->{'SupplierGLN'} = 'TEST_SUPPLIER_GLN';
        $group->{'Items'} = [$item];
        
        $data = new \stdClass();
        $data->{'Groups'} = [$group];
        
        $expectedData = [
            [
                'ean' => '12345',
                'brand' => 'TEST_BRAND',
                'code' => 'TEST_CODE',
                'supplier_code' => 'TEST_SUPPLIER_CODE',
                'supplier_gln' => 'TEST_SUPPLIER_GLN',
                'amount' => 5,
                'noos' => false,
                'timestamp' => '2017-06-18 16:43:32',
            ],
        ];
        
        $this->connection->expects($this->exactly(1))->method('beginTransaction');
        
        $this->connection->expects($this->exactly(1))->method('insertOnDuplicate')
            ->with(self::TEST_PREFIX.'stockbase_stock', $expectedData);
        $this->connection->expects($this->exactly(1))->method('commit');

        $resourceModel = $this->createResourceModel();

        $result = $resourceModel->updateFromStockObject($data);
        $this->assertEquals(1, $result);
    }

    /**
     * testUpdateFromStockObjectEmpty
     */
    public function testUpdateFromStockObjectEmpty()
    {
        $data = new \stdClass();
        $data->{'Groups'} = [];

        $this->connection->expects($this->never())->method('insertOnDuplicate');

        $resourceModel = $this->createResourceModel();

        $result = $resourceModel->updateFromStockObject($data);
        $this->assertEquals(0, $result);
    }
    
    protected function createResourceModel()
    {
        return new StockItem($this->context);
    }
}
