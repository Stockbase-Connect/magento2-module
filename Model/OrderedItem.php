<?php


namespace Strategery\Stockbase\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class OrderedItem
 */
class OrderedItem extends AbstractModel
{
    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    /**
     * @param string $orderId
     */
    public function setOrderId($orderId)
    {
        $this->setData('order_id', $orderId);
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
    public function getStockbaseGuid()
    {
        return $this->getData('stockbase_guid');
    }

    /**
     * @param string $stockbaseGuid
     */
    public function setStockbaseGuid($stockbaseGuid)
    {
        $this->setData('stockbase_guid', $stockbaseGuid);
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
        $this->_init(ResourceModel\OrderedItem::class);
    }
}
