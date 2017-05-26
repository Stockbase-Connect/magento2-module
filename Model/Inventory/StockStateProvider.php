<?php


namespace Strategery\Stockbase\Model\Inventory;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Math\Division as MathDivision;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\ObjectManagerInterface;
use Strategery\Stockbase\Model\Config\StockbaseConfiguration;
use Strategery\Stockbase\Model\StockItemFactory;

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
     * @param StockbaseConfiguration $config
     * @param StockbaseStockManagement $stockbaseStockManagement
     * @param StockItemFactory $stockItemFactory
     * @param MathDivision $mathDivision
     * @param FormatInterface $localeFormat
     * @param ObjectFactory $objectFactory
     * @param ProductFactory $productFactory
     * @param bool $qtyCheckApplicable
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
    
    protected function getStockbaseStockManagement()
    {
        // Lazy initialization to avoid circular dependency injection
        if ($this->stockbaseStockManagement == null) {
            $this->stockbaseStockManagement = $this->objectManager->create(StockbaseStockManagement::class);
        }
        return $this->stockbaseStockManagement;
    }
    
    /**
     * Check quantity
     *
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @exception \Magento\Framework\Exception\LocalizedException
     * @return bool
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
     * Retrieve stock qty whether product is composite or no
     *
     * @param StockItemInterface $stockItem
     * @return float
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getStockQty(StockItemInterface $stockItem)
    {
        $qty = parent::getStockQty($stockItem);
        $qty += $this->getStockbaseStockManagement()->getStockbaseStockAmount($stockItem->getProductId());
        
        return $qty;
    }
}
