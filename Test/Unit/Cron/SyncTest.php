<?php

namespace Stockbase\Integration\Test\Unit\Cron;

use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Stockbase\Integration\Cron\Sync;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\Model\ResourceModel\StockItem;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClient;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClientFactory;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class SyncTest extends TestCase
{
    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;
    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $om;
    /** @var StockbaseClientFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $clientFactory;
    /** @var StockbaseConfiguration|\PHPUnit_Framework_MockObject_MockObject */
    private $config;

    /**
     * setUp
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->setMethods(['log', 'warning', 'notice', 'debug', 'info', 'emergency', 'alert', 'critical', 'error'])
            ->getMock();

        $this->om = $this->createMock(ObjectManagerInterface::class);
        $this->config = $this->createMock(StockbaseConfiguration::class);

        $getStockResponseMock = $this->createMock(\stdClass::class);
        $client = $this->createMock(StockbaseClient::class);
        $client->expects($this->any())->method('getStock')->willReturn(
            $getStockResponseMock
        );

        $this->clientFactory = $this->createMock(StockbaseClientFactory::class);
        $this->clientFactory->expects($this->any())->method('create')->willReturn($client);
    }

    /**
     * testExecuteDisabled
     * @return void
     */
    public function testExecuteDisabled()
    {
        $this->logger->expects(self::never())->method('info');
        $sync = new Sync($this->logger, $this->om, $this->clientFactory, $this->config);
        $sync->execute();
    }

    /**
     * testExecute
     * @param int $updates Number of stock updates received from API
     * @return void
     * @dataProvider executeProvider
     */
    public function testExecuteEnabled(int $updates)
    {
        /** @var StockItem|\PHPUnit_Framework_MockObject_MockObject $stockItemResource */
        $stockItemResource = $this->createMock(StockItem::class);
        $stockItemResource->expects($this->once())
            ->method('updateFromStockObject')
            ->with(self::isInstanceOf(\stdClass::class))
            ->willReturn($updates);

        $this->config->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->om->expects($this->once())->method('create')
            ->with(StockItem::class)
            ->willReturn($stockItemResource);

        $sync = new Sync($this->logger, $this->om, $this->clientFactory, $this->config);
        $sync->execute();
    }

    /**
     * executeProvider
     * @return array
     */
    public function executeProvider()
    {
        return [
            [0],
            [1],
            [3],
        ];
    }
}
