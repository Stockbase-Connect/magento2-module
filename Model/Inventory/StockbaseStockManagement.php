<?php


namespace Stockbase\Integration\Model\Inventory;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\Model\ResourceModel\StockItem as StockItemResource;
use Stockbase\Integration\Model\ResourceModel\StockItemReserve\Collection as StockItemReserveCollection;
use Stockbase\Integration\Model\StockItemReserve;

/**
 * Class StockbaseStockManagement
 */
class StockbaseStockManagement
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var StockbaseConfiguration
     */
    private $config;
    /**
     * @var ProductFactory
     */
    private $productFactory;
    /**
     * @var StockItemResource
     */
    private $stockItemResource;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * StockbaseStockManagement constructor.
     * @param StockRegistryInterface $stockRegistry
     * @param StockbaseConfiguration $config
     * @param ProductFactory         $productFactory
     * @param StockItemResource      $stockItemResource
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        StockbaseConfiguration $config,
        ProductFactory $productFactory,
        StockItemResource $stockItemResource,
        ObjectManagerInterface $objectManager
    ) {

        $this->stockRegistry = $stockRegistry;
        $this->config = $config;
        $this->productFactory = $productFactory;
        $this->stockItemResource = $stockItemResource;
        $this->objectManager = $objectManager;
    }

    /**
     * Gets the amount of items available in the Stockbase stock.
     *
     * @param int $productId
     * @return float
     */
    public function getStockbaseStockAmount($productId)
    {
        $qty = 0;
        
        $ean = $this->getStockbaseEan($productId);
        if (!empty($ean)) {
            $qty += max($this->stockItemResource->getNotReservedStockAmount($ean), 0);
        }
        
        return $qty;
    }

    /**
     * Gets the Stockbase EAN for given product (if any).
     *
     * @param int $productId
     * @return string|null
     */
    public function getStockbaseEan($productId)
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        if ($this->config->isModuleEnabled() &&
            $stockItem->getManageStock() &&
            $stockItem->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO
        ) {
            $product = $this->productFactory->create();
            $product->load($productId);
            $ean = $product->getData($this->config->getEanFieldName());

            if ($product->getData('stockbase_product') && !empty($ean)) {
                return $ean;
            }
        }
        
        return null;
    }

    /**
     * Checks if given product is properly configured to be processed as a Stockbase product.
     *
     * @param int $productId
     * @return bool
     */
    public function isStockbaseProduct($productId)
    {
        return !empty($this->getStockbaseEan($productId));
    }

    /**
     * Increments/decrements the amount of items in stock.
     *
     * @param string $ean
     * @param float  $amount
     * @param string $operation
     */
    public function updateStockAmount($ean, $amount, $operation = '-')
    {
        $this->stockItemResource->updateStockAmount($ean, $amount, $operation);
    }

    /**
     * Creates a stock reserve for given item.
     *
     * @param QuoteItem $quoteItem
     * @param float     $stockbaseAmount
     * @param float     $magentoStockAmount
     * @return StockItemReserve
     */
    public function createReserve(QuoteItem $quoteItem, $stockbaseAmount, $magentoStockAmount)
    {
        $ean = $this->getStockbaseEan($quoteItem->getProductId());
        
        /** @var StockItemReserve $stockItemReserve */
        $stockItemReserve = $this->objectManager->create(StockItemReserve::class);
        $stockItemReserve->setData([
            'ean' => $ean,
            'amount' => $stockbaseAmount,
            'magento_stock_amount' => $magentoStockAmount,
            'quote_item_id' => $quoteItem->getId(),
            'product_id' => $quoteItem->getProductId(),
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
        $stockItemReserve->save();
        
        return $stockItemReserve;
    }
    
    /**
     * Releases the stock reserve.
     *
     * @param StockItemReserve $reserve
     */
    public function releaseReserve(StockItemReserve $reserve)
    {
        $reserve->delete();
    }

    /**
     * Gets stock reserve entries for given products.
     *
     * @param int|int[] $productIds
     * @return StockItemReserve[]
     */
    public function getReserveForProduct($productIds)
    {
        $productIds = is_array($productIds) ? $productIds : [$productIds];

        /** @var StockItemReserveCollection $reserveCollection */
        $reserveCollection = $this->objectManager->create(StockItemReserveCollection::class);
        $reserveCollection->addFieldToFilter('product_id', ['in' => $productIds]);

        /** @var StockItemReserve[] $reservedStockbaseItems */
        $reservedStockbaseItems = $reserveCollection->getItems();

        return $reservedStockbaseItems;
    }

    /**
     * Gets stock reserve entries for given quote items.
     *
     * @param int|int[] $quoteItemIds
     * @return StockItemReserve[]
     */
    public function getReserveForQuoteItem($quoteItemIds)
    {
        $quoteItemIds = is_array($quoteItemIds) ? $quoteItemIds : [$quoteItemIds];
        
        /** @var StockItemReserveCollection $reserveCollection */
        $reserveCollection = $this->objectManager->create(StockItemReserveCollection::class);
        $reserveCollection->addFieldToFilter('quote_item_id', ['in' => $quoteItemIds]);

        /** @var StockItemReserve[] $reservedStockbaseItems */
        $reservedStockbaseItems = $reserveCollection->getItems();
        
        return $reservedStockbaseItems;
    }
}
