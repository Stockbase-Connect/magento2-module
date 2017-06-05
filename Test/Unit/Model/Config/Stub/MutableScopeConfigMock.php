<?php


namespace Stockbase\Integration\Test\Unit\Model\Config\Stub;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class MutableScopeConfigMock
 */
class MutableScopeConfigMock implements MutableScopeConfigInterface
{
    private $data = [];

    /**
     * {@inheritdoc}
     */
    public function setValue(
        $path,
        $value,
        $scopeType = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        $key = sprintf('%s|%s|%s', $path, $scopeType, $scopeCode);
        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $key = sprintf('%s|%s|%s', $path, $scopeType, $scopeCode);
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isSetFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $key = sprintf('%s|%s|%s', $path, $scopeType, $scopeCode);
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        return null;
    }
}
