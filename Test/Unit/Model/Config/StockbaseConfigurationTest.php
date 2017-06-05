<?php

namespace Stockbase\Integration\Test\Unit\Model\Config;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Stockbase\Integration\Model\Config\Source\Environment;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\Test\Unit\Model\Config\Stub\MutableScopeConfigMock;

/**
 * Test for StockbaseConfiguration
 */
class StockbaseConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StockbaseConfiguration
     */
    protected $config;

    /**
     * @var MutableScopeConfigInterface
     */
    protected $configScope;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->configScope = new MutableScopeConfigMock();
        $this->config = new StockbaseConfiguration($this->configScope);
    }

    /**
     *
     */
    public function testDefaultValues()
    {
        $this->assertFalse($this->config->isModuleEnabled());
        $this->assertEquals(Environment::STAGING, $this->config->getEnvironment());
        $this->assertNull($this->config->getUsername());
        $this->assertNull($this->config->getPassword());
        $this->assertNull($this->config->getEanFieldName());
        $this->assertNull($this->config->getOrderPrefix());
    }

    /**
     *
     */
    public function testGetters()
    {
        $this->configScope->setValue(StockbaseConfiguration::CONFIG_MODULE_ENABLED, '1');
        $this->configScope->setValue(StockbaseConfiguration::CONFIG_ENVIRONMENT, 'test_environment');
        $this->configScope->setValue(StockbaseConfiguration::CONFIG_USERNAME, 'test_username');
        $this->configScope->setValue(StockbaseConfiguration::CONFIG_PASSWORD, 'test_password');
        $this->configScope->setValue(StockbaseConfiguration::CONFIG_EAN_FIELD, 'test_ean_field');
        $this->configScope->setValue(StockbaseConfiguration::CONFIG_ORDER_PREFIX, 'test_order_prefix');
        
        $this->assertTrue($this->config->isModuleEnabled());
        $this->assertEquals('test_environment', $this->config->getEnvironment());
        $this->assertEquals('test_username', $this->config->getUsername());
        $this->assertEquals('test_password', $this->config->getPassword());
        $this->assertEquals('test_ean_field', $this->config->getEanFieldName());
        $this->assertEquals('test_order_prefix', $this->config->getOrderPrefix());
    }
}
