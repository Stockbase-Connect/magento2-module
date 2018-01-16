<?php

namespace Stockbase\Integration\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ProductImage
 * @package Stockbase\Integration\Model
 */
class ProductImage extends AbstractModel
{

    /**
     * Constructor.
     */
    protected function _construct()
    {
        $this->_init(\Stockbase\Integration\Model\ResourceModel\ProductImage::class);
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
    public function getImage()
    {
        return $this->getData('image');
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->setData('image', $image);
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

}
