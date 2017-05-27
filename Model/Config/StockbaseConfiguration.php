<?php


namespace Strategery\Stockbase\Model\Config;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Strategery\Stockbase\Model\Config\Source\Environment;

/**
 * Stockbase main mobule configuration resource wrapper.
 */
class StockbaseConfiguration
{
    const CONFIG_MODULE_ENABLED = 'stockbase_section/general/module_enabled';
    const CONFIG_ENVIRONMENT = 'stockbase_section/general/environment';
    const CONFIG_USERNAME = 'stockbase_section/general/username';
    const CONFIG_PASSWORD = 'stockbase_section/general/password';
    const CONFIG_EAN_FIELD = 'stockbase_section/general/ean_field';
    const CONFIG_ORDER_PREFIX = 'stockbase_section/general/order_prefix';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * @var Config
     */
    private $configResource;

    /**
     * StockbaseConfiguration constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Config               $configResource
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Config $configResource)
    {
        $this->scopeConfig = $scopeConfig;
        $this->configResource = $configResource;

        if (!$this->scopeConfig->getValue(self::CONFIG_ENVIRONMENT)) {
            $this->configResource->saveConfig(
                self::CONFIG_ENVIRONMENT,
                Environment::STAGING,
                'default',
                0
            );
        }
        if (!$this->scopeConfig->getValue(self::CONFIG_ORDER_PREFIX)) {
            $this->configResource->saveConfig(
                self::CONFIG_ORDER_PREFIX,
                'MAGE-'.mt_rand(0, 999),
                'default',
                0
            );
        }
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_MODULE_ENABLED);
    }

    /**
     * Gets Stockbase environment name.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->scopeConfig->getValue(self::CONFIG_ENVIRONMENT);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->scopeConfig->getValue(self::CONFIG_USERNAME);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PASSWORD);
    }

    /**
     * @return string
     */
    public function getEanFieldName()
    {
        return $this->scopeConfig->getValue(self::CONFIG_EAN_FIELD);
    }

    /**
     * @return string
     */
    public function getOrderPrefix()
    {
        return $this->scopeConfig->getValue(self::CONFIG_ORDER_PREFIX);
    }
}
