<?php


namespace Strategery\Stockbase\Api\Client;

use Strategery\Stockbase\Model\Config\StockbaseConfiguration;
use DivideBV\PHPDivideIQ\DivideIQ;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

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
     * @var DivideIQ
     */
    protected $divideIqClient;

    /**
     * @var StockbaseConfiguration
     */
    protected $config;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        StockbaseConfiguration $config,
        $instanceName = StockbaseClient::class
    )
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->logger = $logger;
        $this->config = $config;
        
        //TODO: Cache authentication state using DivideIQ::fromJson() and DivideIQ::toJson()
        
        $this->divideIqClient = new DivideIQ(
            $this->config->getUsername(),
            $this->config->getPassword(),
            $this->config->getEnvironment()
        );
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return StockbaseClient
     */
    public function create(array $data = [])
    {
        $data = array_merge([
            'divideIqClient' => $this->divideIqClient,
        ], $data);
        return $this->objectManager->create($this->instanceName, $data);
    }
}
