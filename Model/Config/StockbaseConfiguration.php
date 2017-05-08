<?php


namespace Strategery\Stockbase\Model\Config;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Strategery\Stockbase\Model\Config\Source\Environment;

class StockbaseConfiguration
{
    const CONFIG_MODULE_ENABLED = 'stockbase_section/general/module_enabled';
    const CONFIG_ENVIRONMENT = 'stockbase_section/general/environment';
    const CONFIG_USERNAME = 'stockbase_section/general/username';
    const CONFIG_PASSWORD = 'stockbase_section/general/password';
    const CONFIG_EAN_FIELD = 'stockbase_section/general/ean_field';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * @var Config
     */
    private $configResource;

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
    }

    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(self::CONFIG_MODULE_ENABLED);
    }

    public function getEnvironment()
    {
        return $this->scopeConfig->getValue(self::CONFIG_ENVIRONMENT);
    }

    public function getUsername()
    {
        return $this->scopeConfig->getValue(self::CONFIG_USERNAME);
    }

    public function getPassword()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PASSWORD);
    }

    public function getEanFieldName()
    {
        return $this->scopeConfig->getValue(self::CONFIG_EAN_FIELD);
    }
}
