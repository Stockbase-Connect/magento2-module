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
        return parent::verifyStock($this->ensureStockbaseStockItem($stockItem));
    }

    /**
     * {@inheritdoc}
     */
    public function checkQuoteItemQty(StockItemInterface $stockItem, $qty, $summaryQty, $origQty = 0)
    {
        return parent::checkQuoteItemQty($this->ensureStockbaseStockItem($stockItem), $qty, $summaryQty, $origQty);
    }
    
    /**
     * {@inheritdoc}
     */
    public function checkQty(StockItemInterface $stockItem, $qty)
    {
        return parent::checkQty($this->ensureStockbaseStockItem($stockItem), $qty);
    }

    /**
     * {@inheritdoc}
     */
    public function getStockQty(StockItemInterface $stockItem)
    {
        return parent::getStockQty($this->ensureStockbaseStockItem($stockItem));
    }

    protected function getStockbaseStockManagement()
    {
        // Lazy initialization to avoid circular dependency injection
        if ($this->stockbaseStockManagement == null) {
            $this->stockbaseStockManagement = $this->objectManager->create(StockbaseStockManagement::class);
        }

        return $this->stockbaseStockManagement;
    }

    protected function ensureStockbaseStockItem(StockItemInterface $stockItem)
    {
        if (!($stockItem instanceof CombinedStockbaseStockItem)) {
            $stockbaseQty = $this->getStockbaseStockManagement()->getStockbaseStockAmount($stockItem->getProductId());
            $stockItem = new CombinedStockbaseStockItem($stockItem, $stockbaseQty);
        }
        
        return $stockItem;
    }
}
