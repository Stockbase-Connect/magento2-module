<?php

namespace Stockbase\Integration\Cron;

use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClientFactory;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\Model\ResourceModel\ProductImage as ProductImageResource;
use Stockbase\Integration\Helper\Images as ImagesHelper;

/**
 * Stockbase images synchronization cron job.
 */
class Images
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var StockbaseClientFactory
     */
    private $stockbaseClientFactory;
    /**
     * @var StockbaseConfiguration
     */
    private $config;
    /**
     * @var ProductImageResource
     */
    private $productImageResource;
    /**
     * @var ImagesHelper
     */
    private $imagesHelper;
    /**
     * @var ProductCollection
     */
    private $productCollection;
    /**
     * @var array
     */
    private $eans = array();
    
    /**
     * Images constructor.
     * @param LoggerInterface        $logger
     * @param StockbaseClientFactory $stockbaseClientFactory
     * @param StockbaseConfiguration $config
     * @param ProductCollection      $productCollection
     * @param ProductImageResource   $productImageResource
     * @param ImagesHelper           $imagesHelper
     */
    public function __construct(
        LoggerInterface $logger,
        StockbaseClientFactory $stockbaseClientFactory,
        StockbaseConfiguration $config,
        ProductCollection $productCollection,
        ProductImageResource $productImageResource,
        ImagesHelper $imagesHelper
    ) {
        $this->logger = $logger;
        $this->stockbaseClientFactory = $stockbaseClientFactory;
        $this->config = $config;
        $this->productCollection = $productCollection;
        $this->productImageResource = $productImageResource;
        $this->imagesHelper = $imagesHelper;
    }

    /**
     * Executes the job.
     */
    public function execute()
    {
        // validate configuration:
        if (!$this->config->isModuleEnabled() || !$this->config->isImagesSyncCronEnabled()) {
            return;
        }
        // start process:
        $this->logger->info('Synchronizing Stockbase images...');
        // get all the eans:
        $eans = $this->getEansToProcess();
        try {
            // if still need to process eans:
            if (count($eans) > 0) {
                $client = $this->stockbaseClientFactory->create();
                $images = $client->getImages($eans);
                // validate returned images:
                if (is_array($images->{'Items'}) && count($images->{'Items'}) > 0) {
                    // download and save the images locally:
                    $newImagesCount = $this->imagesHelper->saveProductImages($images->{'Items'}, $client);
                    $this->logger->info('New synchronized images: '.$newImagesCount);
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Cron runImageImport error: '.$e->getMessage());
            
            return;
        }
        $this->logger->info('Stockbase images synchronization complete.');
    }

    /**
     * @param Product $product
     */
    public function getProductEan($product)
    {
        // get ean attribute:
        $attribute = $this->config->getEanFieldName();
        // get ean:
        $ean = $product->getData($attribute);
        // if the ean is not empty:
        if ($ean) {
            // add the ean if this product has one:
            $this->eans[] = $ean;
        }
    }

    /**
     * @return array
     */
    private function getEansToProcess()
    {
        // clean eans list:
        $this->eans = array();
        // start process:
        $this->logger->info('Get All EANs');
        // get ean attribute:
        $attribute = $this->config->getEanFieldName();
        // validate attribute:
        if ($attribute) {
            // create collection and apply filters:
            $collection = $this->productCollection->create()
                ->addAttributeToSelect($attribute)
                ->addAttributeToSelect('stockbase_product')
                ->addAttributeToFilter('stockbase_product', array('eq' => '1')) // only stockbase products
                ->addAttributeToFilter($attribute, array('notnull' => true, 'neq' => '')) // not null and not empty ean
                ;
            // if the filter is active then exclude the eans processed:
            if ($this->config->filterProcessedProducts()) {
                // get eans list:
                $processedEans = $this->productImageResource->getProcessedEans();
                // add eans filter:
                if (count($processedEans) > 0) {
                    $this->logger->info('Filtered EANs: '.count($processedEans));
                    $collection->addAttributeToFilter($attribute, array('nin' => $processedEans));
                }
            }
            // walk collection and save eans in the object:
            $collection->walk(array($this, 'getProductEan'));
            // log eans count:
            $this->logger->info('EANs to process: '.count($this->eans));
        } else {
            // missing ean attribute:
            $this->logger->info('Please setup the EAN attribute.');
        }
        
        return $this->eans;
    }
}
