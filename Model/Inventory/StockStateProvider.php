<?php


namespace Strategery\Stockbase\Model\Inventory;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Math\Division as MathDivision;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class StockStateProvider
 */
class StockStateProvider extends \Magento\CatalogInventory\Model\StockStateProvider
{
    /**
     * @var StockbaseStockManagement
     */
    private $stockbaseStockManagement;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param MathDivision           $mathDivision
     * @param FormatInterface        $localeFormat
     * @param ObjectFactory          $objectFactory
     * @param ProductFactory         $productFactory
     * @param bool                   $qtyCheckApplicable
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MathDivision $mathDivision,
        FormatInterface $localeFormat,
        ObjectFactory $objectFactory,
        ProductFactory $productFactory,
        $qtyCheckApplicable = true
    ) {
        parent::__construct($mathDivision, $localeFormat, $objectFactory, $productFactory, $qtyCheckApplicable);
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function verifyStock(StockItemInterface $stockItem)
    {
        if ($stockItem->getQty() === null && $stockItem->getManageStock()) {
            return false;
        }
        if ($stockItem->getBackorders() == StockItemInterface::BACKORDERS_NO) {
            $stockbaseQty = $this->getStockbaseStockManagement()->getStockbaseStockAmount($stockItem->getProductId());
            
            $qty = $stockItem->getQty() + $stockbaseQty;
            if ($qty <= $stockItem->getMinQty()) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function checkQty(StockItemInterface $stockItem, $qty)
    {
        if (!$this->qtyCheckApplicable) {
            return true;
        }
        if (!$stockItem->getManageStock()) {
            return true;
        }
        $stockbaseQty = $this->getStockbaseStockManagement()->getStockbaseStockAmount($stockItem->getProductId());
        
        if ($stockItem->getQty() + $stockbaseQty - $stockItem->getMinQty() - $qty < 0) {
            switch ($stockItem->getBackorders()) {
                case \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NONOTIFY:
                case \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    return false;
            }
        }
        
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getStockQty(StockItemInterface $stockItem)
    {
        $qty = parent::getStockQty($stockItem);
        $qty += $this->getStockbaseStockManagement()->getStockbaseStockAmount($stockItem->getProductId());
        
        return $qty;
    }

    protected function getStockbaseStockManagement()
    {
        // Lazy initialization to avoid circular dependency injection
        if ($this->stockbaseStockManagement == null) {
            $this->stockbaseStockManagement = $this->objectManager->create(StockbaseStockManagement::class);
        }

        return $this->stockbaseStockManagement;
    }
}
