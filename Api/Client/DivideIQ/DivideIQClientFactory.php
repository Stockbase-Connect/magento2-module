<?php


namespace Strategery\Stockbase\Api\Client\DivideIQ;

use DivideBV\PHPDivideIQ\DivideIQ;
use Magento\Framework\ObjectManagerInterface;
use Strategery\Stockbase\Model\Config\StockbaseConfiguration;

/**
 * DivideIQClient Factory.
 */
class DivideIQClientFactory
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
     * DivideIQClientFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param StockbaseConfiguration $config
     * @param string                 $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        StockbaseConfiguration $config,
        $instanceName = DivideIQClient::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->config = $config;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return DivideIQ
     */
    public function create(array $data = [])
    {
        //TODO: Cache authentication state using DivideIQ::fromJson() and DivideIQ::toJson()
        
        $data = array_merge([
            'username' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
            'environment' => $this->config->getEnvironment(),
        ], $data);
        
        return $this->objectManager->create($this->instanceName, $data);
    }
}
