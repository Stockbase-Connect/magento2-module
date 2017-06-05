<?php


namespace Stockbase\Integration\Model\Inventory;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Class CombinedStockbaseStockItem
 */
class CombinedStockbaseStockItem implements StockItemInterface
{
    /**
     * @var StockItemInterface
     */
    private $magentoStockItem;
    
    /**
     * @var float
     */
    private $stockbaseQty;

    /**
     * CombinedStockbaseStockItem constructor.
     * @param StockItemInterface $magentoStockItem
     * @param float              $stockbaseQty
     */
    public function __construct(StockItemInterface $magentoStockItem, $stockbaseQty)
    {
        $this->magentoStockItem = $magentoStockItem;
        $this->stockbaseQty = $stockbaseQty;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        // @codingStandardsIgnoreStart
        return call_user_func_array([$this->magentoStockItem, $name], $arguments);
        // @codingStandardsIgnoreEnd
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->magentoStockItem->getQty() + $this->stockbaseQty;
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        throw new \Exception('Can not set quantity on combined Stockbase stock item.');
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->magentoStockItem->getItemId();
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($itemId)
    {
        $this->magentoStockItem->setItemId($itemId);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->magentoStockItem->getProductId();
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        $this->magentoStockItem->setProductId($productId);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStockId()
    {
        return $this->magentoStockItem->getStockId();
    }

    /**
     * {@inheritdoc}
     */
    public function setStockId($stockId)
    {
        $this->magentoStockItem->setStockId($stockId);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsInStock()
    {
        return $this->magentoStockItem->getIsInStock();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInStock($isInStock)
    {
        $this->magentoStockItem->setIsInStock($isInStock);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsQtyDecimal()
    {
        return $this->magentoStockItem->getIsQtyDecimal();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsQtyDecimal($isQtyDecimal)
    {
        $this->magentoStockItem->setIsQtyDecimal($isQtyDecimal);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowDefaultNotificationMessage()
    {
        return $this->magentoStockItem->getShowDefaultNotificationMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function getUseConfigMinQty()
    {
        return $this->magentoStockItem->getUseConfigMinQty();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseConfigMinQty($useConfigMinQty)
    {
        $this->magentoStockItem->setUseConfigMinQty($useConfigMinQty);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinQty()
    {
        return $this->magentoStockItem->getMinQty();
    }

    /**
     * {@inheritdoc}
     */
    public function setMinQty($minQty)
    {
        $this->magentoStockItem->setMinQty($minQty);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUseConfigMinSaleQty()
    {
        return $this->magentoStockItem->getUseConfigMinSaleQty();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseConfigMinSaleQty($useConfigMinSaleQty)
    {
        $this->magentoStockItem->setUseConfigMinSaleQty($useConfigMinSaleQty);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMinSaleQty()
    {
        return $this->magentoStockItem->getMinSaleQty();
    }

    /**
     * {@inheritdoc}
     */
    public function setMinSaleQty($minSaleQty)
    {
        $this->magentoStockItem->setMinSaleQty($minSaleQty);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUseConfigMaxSaleQty()
    {
        return $this->magentoStockItem->getUseConfigMaxSaleQty();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseConfigMaxSaleQty($useConfigMaxSaleQty)
    {
        $this->magentoStockItem->setUseConfigMaxSaleQty($useConfigMaxSaleQty);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSaleQty()
    {
        return $this->magentoStockItem->getMaxSaleQty();
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxSaleQty($maxSaleQty)
    {
        $this->magentoStockItem->setMaxSaleQty($maxSaleQty);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUseConfigBackorders()
    {
        return $this->magentoStockItem->getUseConfigBackorders();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseConfigBackorders($useConfigBackorders)
    {
        $this->magentoStockItem->setUseConfigBackorders($useConfigBackorders);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackorders()
    {
        return $this->magentoStockItem->getBackorders();
    }

    /**
     * {@inheritdoc}
     */
    public function setBackorders($backOrders)
    {
        $this->magentoStockItem->setBackorders($backOrders);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUseConfigNotifyStockQty()
    {
        return $this->magentoStockItem->getUseConfigNotifyStockQty();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseConfigNotifyStockQty($useConfigNotifyStockQty)
    {
        $this->magentoStockItem->setUseConfigNotifyStockQty($useConfigNotifyStockQty);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotifyStockQty()
    {
        return $this->magentoStockItem->getNotifyStockQty();
    }

    /**
     * {@inheritdoc}
     */
    public function setNotifyStockQty($notifyStockQty)
    {
        $this->magentoStockItem->setNotifyStockQty($notifyStockQty);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUseConfigQtyIncrements()
    {
        return $this->magentoStockItem->getUseConfigQtyIncrements();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseConfigQtyIncrements($useConfigQtyIncrements)
    {
        $this->magentoStockItem->setUseConfigQtyIncrements($useConfigQtyIncrements);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyIncrements()
    {
        return $this->magentoStockItem->getQtyIncrements();
    }

    /**
     * {@inheritdoc}
     */
    public function setQtyIncrements($qtyIncrements)
    {
        $this->magentoStockItem->setQtyIncrements($qtyIncrements);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUseConfigEnableQtyInc()
    {
        return $this->magentoStockItem->getUseConfigEnableQtyInc();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseConfigEnableQtyInc($useConfigEnableQtyInc)
    {
        $this->magentoStockItem->setUseConfigEnableQtyInc($useConfigEnableQtyInc);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableQtyIncrements()
    {
        return $this->magentoStockItem->getEnableQtyIncrements();
    }

    /**
     * {@inheritdoc}
     */
    public function setEnableQtyIncrements($enableQtyIncrements)
    {
        $this->magentoStockItem->setEnableQtyIncrements($enableQtyIncrements);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUseConfigManageStock()
    {
        return $this->magentoStockItem->getUseConfigManageStock();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseConfigManageStock($useConfigManageStock)
    {
        $this->magentoStockItem->setUseConfigManageStock($useConfigManageStock);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getManageStock()
    {
        return $this->magentoStockItem->getManageStock();
    }

    /**
     * {@inheritdoc}
     */
    public function setManageStock($manageStock)
    {
        $this->magentoStockItem->setManageStock($manageStock);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLowStockDate()
    {
        return $this->magentoStockItem->getLowStockDate();
    }

    /**
     * {@inheritdoc}
     */
    public function setLowStockDate($lowStockDate)
    {
        $this->magentoStockItem->setLowStockDate($lowStockDate);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDecimalDivided()
    {
        return $this->magentoStockItem->getIsDecimalDivided();
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDecimalDivided($isDecimalDivided)
    {
        $this->magentoStockItem->setIsDecimalDivided($isDecimalDivided);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStockStatusChangedAuto()
    {
        return $this->magentoStockItem->getStockStatusChangedAuto();
    }

    /**
     * {@inheritdoc}
     */
    public function setStockStatusChangedAuto($stockStatusChangedAuto)
    {
        $this->magentoStockItem->setStockStatusChangedAuto($stockStatusChangedAuto);
        
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->magentoStockItem->getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\StockItemExtensionInterface $extensionAttributes
    ) {
        $this->magentoStockItem->setExtensionAttributes($extensionAttributes);
        
        return $this;
    }
}
