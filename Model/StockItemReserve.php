<?php


namespace Strategery\Stockbase\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class StockItemReserve
 */
class StockItemReserve extends AbstractModel
{
    /**
     * @return mixed
     */
    public function getEan()
    {
        return $this->getData('ean');
    }

    /**
     * @param string $ean
     */
    public function setEan($ean)
    {
        $this->setData('ean', $ean);
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->getData('amount');
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->setData('amount', $amount);
    }

    /**
     * @return mixed
     */
    public function getMagentoStockAmount()
    {
        return $this->getData('magento_stock_amount');
    }

    /**
     * @param string $magentoStockAmount
     */
    public function setMagentoStockAmount($magentoStockAmount)
    {
        $this->setData('magento_stock_amount', $magentoStockAmount);
    }

    /**
     * @return mixed
     */
    public function getQuoteItemId()
    {
        return $this->getData('quote_item_id');
    }

    /**
     * @param string $quoteItemId
     */
    public function setQuoteItemId($quoteItemId)
    {
        $this->setData('quote_item_id', $quoteItemId);
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getData('product_id');
    }

    /**
     * @param string $productId
     */
    public function setProductId($productId)
    {
        $this->setData('product_id', $productId);
    }

    /**
     * @return mixed
     */
    public function getOrderItemId()
    {
        return $this->getData('order_item_id');
    }

    /**
     * @param string $orderItemId
     */
    public function setOrderItemId($orderItemId)
    {
        $this->setData('order_item_id', $orderItemId);
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * @param string $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData('created_at', $createdAt);
    }

    protected function _construct()
    {
        $this->_init(\Strategery\Stockbase\Model\ResourceModel\StockItemReserve::class);
    }
}
