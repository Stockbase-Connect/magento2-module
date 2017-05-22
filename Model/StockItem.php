<?php


namespace Strategery\Stockbase\Model;

use Magento\Framework\Model\AbstractModel;

class StockItem extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Strategery\Stockbase\Model\ResourceModel\StockItem::class);
    }

    public function getEan()
    {
        return $this->getId();
    }

    public function setEan($ean)
    {
        $this->setId($ean);
    }

    public function getBrand()
    {
        return $this->getData('brand');
    }

    public function setBrand($brand)
    {
        $this->setData('brand', $brand);
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function setCode($code)
    {
        $this->setData('code', $code);
    }

    public function getSupplierCode()
    {
        return $this->getData('supplier_code');
    }

    public function setSupplierCode($supplierCode)
    {
        $this->setData('supplier_code', $supplierCode);
    }

    public function getSupplierGln()
    {
        return $this->getData('supplier_gln');
    }

    public function setSupplierGln($supplierGln)
    {
        $this->setData('supplier_gln', $supplierGln);
    }

    public function getAmount()
    {
        return $this->getData('amount');
    }

    public function setAmount($amount)
    {
        $this->setData('amount', $amount);
    }

    public function getNoos()
    {
        return (bool)$this->getData('noos');
    }

    public function setNoos($noos)
    {
        $this->setData('noos', $noos);
    }

    public function getTimestamp()
    {
        return $this->getData('timestamp');
    }

    public function setTimestamp($timestamp)
    {
        $this->setData('timestamp', $timestamp);
    }
}
