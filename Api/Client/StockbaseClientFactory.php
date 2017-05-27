<?php


namespace Strategery\Stockbase\Api\Client;

use Strategery\Stockbase\Api\Client\DivideIQ\DivideIQClientFactory;
use Strategery\Stockbase\Model\Config\StockbaseConfiguration;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

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
     * @var StockbaseConfiguration
     */
    protected $config;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var DivideIQClientFactory
     */
    private $divideIQClientFactory;

    /**
     * StockbaseClientFactory constructor.
     * @param LoggerInterface        $logger
     * @param ObjectManagerInterface $objectManager
     * @param StockbaseConfiguration $config
     * @param DivideIQClientFactory  $divideIQClientFactory
     * @param string                 $instanceName
     */
    public function __construct(
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        StockbaseConfiguration $config,
        DivideIQClientFactory $divideIQClientFactory,
        $instanceName = StockbaseClient::class
    ) {
    
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->logger = $logger;
        $this->config = $config;
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
