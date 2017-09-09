<?php


namespace Stockbase\Integration\Test\Unit\StockbaseApi\Client;

use DivideBV\PHPDivideIQ\DivideIQ;
use PHPUnit\Framework\TestCase;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClient;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClientException;

/**
 * Class StockbaseClientTest
 */
class StockbaseClientTest extends TestCase
{
    /** @var DivideIQ|\PHPUnit_Framework_MockObject_MockObject */
    private $divideIqClient;

    /** @var StockbaseConfiguration|\PHPUnit_Framework_MockObject_MockObject */
    private $stockbaseConfiguration;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->divideIqClient = $this->createMock(DivideIQ::class);

        $this->stockbaseConfiguration = $this->createMock(StockbaseConfiguration::class);
    }

    /**
     * testGetStock
     */
    public function testGetStock()
    {
        $expectedData = [
            'Since' => 1497797012,
            'Until' => 1497797015,
        ];
        $this->divideIqClient->expects($this->once())->method('request')
            ->with(StockbaseClient::STOCKBASE_STOCK_ENDPOINT, $expectedData, 'GET')
            ->willReturn('TEST_ANSWER');

        $client = new StockbaseClient($this->divideIqClient, $this->stockbaseConfiguration);
        $this->assertEquals(
            'TEST_ANSWER',
            $client->getStock(new \DateTime('@1497797012'), new \DateTime('@1497797015'))
        );
    }

    /**
     * testGetImages
     */
    public function testGetImages()
    {
        $expectedData = [
            'ean' => '101,102,103,104,105',
        ];

        $this->divideIqClient->expects($this->once())->method('request')
            ->with(StockbaseClient::STOCKBASE_IMAGES_ENDPOINT, $expectedData, 'GET')
            ->willReturn('TEST_ANSWER');

        $client = new StockbaseClient($this->divideIqClient, $this->stockbaseConfiguration);
        $this->assertEquals('TEST_ANSWER', $client->getImages([101, 102, 103, 104, 105]));
    }

    /**
     * testCreateOrder
     */
    public function testCreateOrder()
    {
        $this->stockbaseConfiguration
            ->method('getOrderPrefix')
            ->willReturn('TEST_ORDER_PREFIX');

        $order = $this->createOrderMock();

        $reservedItems = [];
        for ($i = 0; $i < 2; $i++) {
            $reservedItem = $this->createMock(\Stockbase\Integration\Model\StockItemReserve::class);
            $reservedItem->method('getEan')->willReturn($i * 100 + 1);
            $reservedItem->method('getAmount')->willReturn($i * 100 + 2);

            $reservedItems[$i] = $reservedItem;
        }

        $this->divideIqClient->expects($this->once())->method('request')
            ->with(
                StockbaseClient::STOCKBASE_ORDER_REQUEST_ENDPOINT,
                $this->anything(),
                'POST'
            )
            ->willReturnCallback(
                function ($serviceName, $payload = [], $method = 'GET') use ($order, $reservedItems) {
                    $this->assertTrue(isset($payload['OrderHeader']['OrderNumber']));
                    $this->assertEquals(
                        'TEST_ORDER_PREFIX#'.$order->getRealOrderId(),
                        $payload['OrderHeader']['OrderNumber']
                    );
                    $this->assertTrue(isset($payload['OrderHeader']['TimeStamp']));
                    $this->assertRegExp(
                        '/^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$/',
                        $payload['OrderHeader']['TimeStamp']
                    );
                    $this->assertTrue(isset($payload['OrderHeader']['Attention']));
                    $this->assertEquals($order->getCustomerNote(), $payload['OrderHeader']['Attention']);

                    $this->assertTrue(isset($payload['OrderLines']));
                    $this->assertCount(count($reservedItems), $payload['OrderLines']);

                    $this->assertTrue(isset($payload['OrderDelivery']['Person']['FirstName']));
                    $this->assertEquals(
                        $order->getShippingAddress()->getFirstname(),
                        $payload['OrderDelivery']['Person']['FirstName']
                    );
                    $this->assertTrue(isset($payload['OrderDelivery']['Person']['Surname']));
                    $this->assertEquals(
                        $order->getShippingAddress()->getLastname(),
                        $payload['OrderDelivery']['Person']['Surname']
                    );
                    $this->assertTrue(isset($payload['OrderDelivery']['Person']['Company']));
                    $this->assertEquals(
                        $order->getShippingAddress()->getCompany(),
                        $payload['OrderDelivery']['Person']['Company']
                    );

                    $this->assertTrue(isset($payload['OrderDelivery']['Address']['Street']));
                    $this->assertEquals(
                        $order->getShippingAddress()->getStreetLine(1),
                        $payload['OrderDelivery']['Address']['Street']
                    );
                    $this->assertTrue(isset($payload['OrderDelivery']['Address']['StreetNumber']));
                    $this->assertEquals(
                        $order->getShippingAddress()->getStreetLine(2),
                        $payload['OrderDelivery']['Address']['StreetNumber']
                    );
                    $this->assertTrue(isset($payload['OrderDelivery']['Address']['ZipCode']));
                    $this->assertEquals(
                        $order->getShippingAddress()->getPostcode(),
                        $payload['OrderDelivery']['Address']['ZipCode']
                    );
                    $this->assertTrue(isset($payload['OrderDelivery']['Address']['City']));
                    $this->assertEquals(
                        $order->getShippingAddress()->getCity(),
                        $payload['OrderDelivery']['Address']['City']
                    );
                    $this->assertTrue(isset($payload['OrderDelivery']['Address']['CountryCode']));
                    $this->assertEquals(
                        $order->getShippingAddress()->getCountryId(),
                        $payload['OrderDelivery']['Address']['CountryCode']
                    );


                    $response = new \stdClass();
                    $response->{'StatusCode'} = 1;

                    return $response;
                }
            );

        $client = new StockbaseClient($this->divideIqClient, $this->stockbaseConfiguration);

        $result = $client->createOrder($order, $reservedItems);

        $this->assertEquals(1, $result->{'StatusCode'});
    }

    /**
     * testCreateOrderApiFail
     */
    public function testCreateOrderApiFail()
    {
        $this->stockbaseConfiguration
            ->method('getOrderPrefix')
            ->willReturn('TEST_ORDER_PREFIX');

        $order = $this->createOrderMock();

        $reservedItems = [];
        for ($i = 0; $i < 2; $i++) {
            $reservedItem = $this->createMock(\Stockbase\Integration\Model\StockItemReserve::class);
            $reservedItem->method('getEan')->willReturn($i * 100 + 1);
            $reservedItem->method('getAmount')->willReturn($i * 100 + 2);

            $reservedItems[$i] = $reservedItem;
        }

        $this->divideIqClient->expects($this->once())->method('request')
            ->with(
                StockbaseClient::STOCKBASE_ORDER_REQUEST_ENDPOINT,
                $this->anything(),
                'POST'
            )
            ->willReturnCallback(
                function () {
                    $item = new \stdClass();
                    $item->{'StatusCode'} = 2;
                    $item->{'ExceptionMessage'} = 'Test exception.';
                    $response = new \stdClass();
                    $response->{'StatusCode'} = 2;
                    $response->{'Items'} = [$item];

                    return $response;
                }
            );

        $client = new StockbaseClient($this->divideIqClient, $this->stockbaseConfiguration);

        $this->expectException(StockbaseClientException::class);
        $this->expectExceptionMessageRegExp('/Test exception\./');

        $client->createOrder($order, $reservedItems);
    }

    protected function createOrderMock()
    {
        $shippingAddress = $this->createMock(\Magento\Sales\Model\Order\Address::class);
        $shippingAddress->method('getFirstname')->willReturn('TEST_FIRST_NAME');
        $shippingAddress->method('getLastname')->willReturn('TEST_LAST_NAME');
        $shippingAddress->method('getCompany')->willReturn('TEST_COMPANY');
        $shippingAddress->method('getStreetLine')->willReturnCallback(
            function ($line) {
                return sprintf('STREET_LINE_%d', $line);
            }
        );
        $shippingAddress->method('getPostcode')->willReturn('TEST_ZIP');
        $shippingAddress->method('getCity')->willReturn('TEST_CITY');
        $shippingAddress->method('getCountryId')->willReturn('US');

        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->method('getShippingAddress')->willReturn($shippingAddress);

        $order->method('getRealOrderId')->willReturn(123456);
        $order->method('getCustomerNote')->willReturn('TEST_CUSTOMER_NOTE');

        return $order;
    }
}
