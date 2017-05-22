<?php


namespace Strategery\Stockbase\Model;

use Magento\Framework\Model\AbstractModel;

class StockItemReserve extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Strategery\Stockbase\Model\ResourceModel\StockItemReserve::class);
    }

    public function getEan()
    {
        return $this->getData('ean');
    }

    public function setEan($ean)
    {
        $this->setData('ean', $ean);
    }

    public function getAmount()
    {
        return $this->getData('amount');
    }

    public function setAmount($amount)
    {
        $this->setData('amount', $amount);
    }

    public function getMagentoStockAmount()
    {
        return $this->getData('magento_stock_amount');
    }

    public function setMagentoStockAmount($magentoStockAmount)
    {
        $this->setData('magento_stock_amount', $magentoStockAmount);
    }

    public function getQuoteItemId()
    {
        return $this->getData('quote_item_id');
    }

    public function setQuoteItemId($quoteItemId)
    {
        $this->setData('quote_item_id', $quoteItemId);
    }

    public function getProductId()
    {
        return $this->getData('product_id');
    }

    public function setProductId($productId)
    {
        $this->setData('product_id', $productId);
    }

    public function getOrderItemId()
    {
        return $this->getData('order_item_id');
    }

    public function setOrderItemId($orderItemId)
    {
        $this->setData('order_item_id', $orderItemId);
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function setCreatedAt($createdAt)
    {
        $this->setData('created_at', $createdAt);
    }
}
