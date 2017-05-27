<?php


namespace Strategery\Stockbase\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class StockItem
 */
class StockItem extends AbstractModel
{
    /**
     * @return string
     */
    public function getEan()
    {
        return $this->getId();
    }

    /**
     * @param string $ean
     */
    public function setEan($ean)
    {
        $this->setId($ean);
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->getData('brand');
    }

    /**
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->setData('brand', $brand);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->setData('code', $code);
    }

    /**
     * @return string
     */
    public function getSupplierCode()
    {
        return $this->getData('supplier_code');
    }

    /**
     * @param string $supplierCode
     */
    public function setSupplierCode($supplierCode)
    {
        $this->setData('supplier_code', $supplierCode);
    }

    /**
     * @return string
     */
    public function getSupplierGln()
    {
        return $this->getData('supplier_gln');
    }

    /**
     * @param string $supplierGln
     */
    public function setSupplierGln($supplierGln)
    {
        $this->setData('supplier_gln', $supplierGln);
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->getData('amount');
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->setData('amount', $amount);
    }

    /**
     * @return bool
     */
    public function getNoos()
    {
        return (bool) $this->getData('noos');
    }

    /**
     * @param bool $noos
     */
    public function setNoos($noos)
    {
        $this->setData('noos', $noos);
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->getData('timestamp');
    }

    /**
     * @param string $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->setData('timestamp', $timestamp);
    }

    protected function _construct()
    {
        $this->_init(\Strategery\Stockbase\Model\ResourceModel\StockItem::class);
    }
}
