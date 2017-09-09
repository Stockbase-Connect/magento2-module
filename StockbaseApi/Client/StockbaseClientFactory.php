<?php


namespace Stockbase\Integration\StockbaseApi\Client;

use Magento\Framework\ObjectManagerInterface;
use Stockbase\Integration\StockbaseApi\Client\DivideIQ\DivideIQClientFactory;

/**
 * StockbaseClient Factory
 */
class StockbaseClientFactory
{

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $instanceName;

    /**
     * @var DivideIQClientFactory
     */
    private $divideIQClientFactory;

    /**
     * StockbaseClientFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param DivideIQClientFactory  $divideIQClientFactory
     * @param string                 $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DivideIQClientFactory $divideIQClientFactory,
        $instanceName = StockbaseClient::class
    ) {

        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->divideIQClientFactory = $divideIQClientFactory;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return StockbaseClient
     */
    public function create(array $data = [])
    {
        if (!isset($data['divideIqClient'])) {
            $data['divideIqClient'] = $this->divideIQClientFactory->create();
        }

        return $this->objectManager->create($this->instanceName, $data);
    }
}
