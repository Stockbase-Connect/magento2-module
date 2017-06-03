<?php


namespace Strategery\Stockbase\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Strategery\Stockbase\Model\Config\Source\Environment;

/**
 * Stockbase main mobule configuration resource wrapper.
 */
class StockbaseConfiguration
{
    const CONFIG_MODULE_ENABLED = 'stockbase/general/module_enabled';
    const CONFIG_ENVIRONMENT = 'stockbase/general/environment';
    const CONFIG_USERNAME = 'stockbase/general/username';
    const CONFIG_PASSWORD = 'stockbase/general/password';
    const CONFIG_EAN_FIELD = 'stockbase/general/ean_field';
    const CONFIG_ORDER_PREFIX = 'stockbase/general/order_prefix';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * StockbaseConfiguration constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
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
        return $this->scopeConfig->getValue(self::CONFIG_ENVIRONMENT) ?: Environment::STAGING;
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
