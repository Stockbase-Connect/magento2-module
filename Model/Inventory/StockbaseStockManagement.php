<?php


namespace Strategery\Stockbase\Model\Inventory;


use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Strategery\Stockbase\Model\Config\StockbaseConfiguration;
use Strategery\Stockbase\Model\ResourceModel\StockItem as StockItemResource;
use Strategery\Stockbase\Model\StockItemReserve;

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

    public function getStockbaseStockAmount($productId)
    {
        $qty = 0;
        
        $ean = $this->getStockbaseEan($productId);
        if ($ean) {
            $qty += max($this->stockItemResource->getNotReservedStockAmount($ean), 0);
        }
        
        return $qty;
    }

    public function getStockbaseEan($productId)
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        if (
            $this->config->isModuleEnabled() &&
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
        
        return false;
    }

    public function isStockbaseProduct($productId)
    {
        return $this->getStockbaseEan($productId) !== false;
    }

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
    
}
