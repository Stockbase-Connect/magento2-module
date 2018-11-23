<?php


namespace Stockbase\Integration\StockbaseApi\Client;

use Webmozart\Assert\Assert;
use DivideBV\PHPDivideIQ\DivideIQ;
use Magento\Sales\Api\Data\OrderInterface;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\Model\StockItemReserve;

/**
 * Stockbase API client.
 */
class StockbaseClient
{
    const STOCKBASE_STOCK_ENDPOINT = 'stockbase_stock';
    const STOCKBASE_IMAGES_ENDPOINT = 'stockbase_images';
    const STOCKBASE_ORDER_REQUEST_ENDPOINT = 'stockbase_orderrequest';
    
    /**
     * @var DivideIQ
     */
    private $divideIqClient;
    
    /**
     * @var StockbaseConfiguration
     */
    private $stockbaseConfiguration;

    /**
     * StockbaseClient constructor.
     * @param DivideIQ               $divideIqClient
     * @param StockbaseConfiguration $stockbaseConfiguration
     */
    public function __construct(
        DivideIQ $divideIqClient,
        StockbaseConfiguration $stockbaseConfiguration
    ) {
        $this->divideIqClient = $divideIqClient;
        $this->stockbaseConfiguration = $stockbaseConfiguration;
    }

    /**
     * Gets current Stockbase stock state.
     *
     * @param \DateTime|null $since
     * @param \DateTime|null $until
     * @return object
     * @throws \Exception
     */
    public function getStock(\DateTime $since = null, \DateTime $until = null)
    {
        $data = [];
        if ($since !== null) {
            $data['Since'] = $since->getTimestamp();
        }
        if ($until !== null) {
            $data['Until'] = $until->getTimestamp();
        }
        
        return $this->divideIqClient->request(self::STOCKBASE_STOCK_ENDPOINT, $data);
    }

    /**
     * Gets images for specified EANs.
     *
     * @param string[] $eans
     * @return object
     * @throws \Exception
     */
    public function getImages(array $eans)
    {
        Assert::allNumeric($eans);
        
        $data = [
            'ean' => implode(',', $eans),
        ];
        
        return $this->divideIqClient->request(self::STOCKBASE_IMAGES_ENDPOINT, $data);
    }

    /**
     * Downloads a file using current client configuration and saves it at the specified destination.
     *
     * @param string|\GuzzleHttp\Url                             $uri         File URI.
     * @param string|resource|\GuzzleHttp\Stream\StreamInterface $destination Destination where the file should be saved to.
     * @return null
     */
    public function downloadImage($uri, $destination)
    {
        return $this->divideIqClient->download($uri, $destination);
    }

    /**
     * Creates an order on Stockbase from reserved items for specified Magento order.
     *
     * @param OrderInterface     $order
     * @param StockItemReserve[] $reservedStockbaseItems
     * @return object
     * @throws \Exception
     */
    public function createOrder(OrderInterface $order, array $reservedStockbaseItems)
    {
        $orderPrefix = $this->stockbaseConfiguration->getOrderPrefix();
        $shippingAddress = $order->getShippingAddress();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $orderLines = [];

        $orderLineNumber = 0;
        foreach ($reservedStockbaseItems as $reserve) {
            $orderLineNumber++;
            $orderLines[] = [
                'Number' => $orderLineNumber, // Number starting from 1
                'EAN' => $reserve->getEan(),
                'Amount' => (int) $reserve->getAmount(),
            ];
        }

        $orderHeader = [
            'OrderNumber' => $orderPrefix.'#'.$order->getRealOrderId(),
            'TimeStamp' => $now->format('Y-m-d h:i:s'),
            'Attention' => $order->getCustomerNote() ? $order->getCustomerNote() : ' ',
        ];
        
        $orderDelivery = [
            'Person' => [
                'FirstName' => $shippingAddress->getFirstname(),
                'Surname' => $shippingAddress->getLastname(),
                'Company' => $shippingAddress->getCompany() ?: ' ',
            ],
            'Address' => [
                'Street' => $shippingAddress->getStreetLine(1),
                'StreetNumber' => $shippingAddress->getStreetLine(2) ?: '-',
                'ZipCode' => $shippingAddress->getPostcode(),
                'City' => $shippingAddress->getCity(),
                'CountryCode' => $shippingAddress->getCountryId(),
            ],
        ];

        $orderRequest = [
            'OrderHeader' => $orderHeader,
            'OrderLines' => $orderLines,
            'OrderDelivery' => $orderDelivery,
        ];

        $response = $this->divideIqClient->request(self::STOCKBASE_ORDER_REQUEST_ENDPOINT, $orderRequest, 'POST');
        if ($response->{'StatusCode'} != 1) {
            $message = '';
            if (isset($response->{'Items'}) && is_array($response->{'Items'})) {
                foreach ($response->{'Items'} as $item) {
                    if ($item->{'StatusCode'} != 1) {
                        $message .= ' '.trim($item->{'ExceptionMessage'});
                    }
                }
            }
            throw new StockbaseClientException('Failed sending order to stockbase.'.$message);
        }
        
        return $response;
    }
}
