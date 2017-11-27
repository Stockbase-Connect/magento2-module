<?php

namespace Stockbase\Integration\Cron;

use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClientFactory;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\Model\ResourceModel\StockItem as StockItemResource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\Url as UrlHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

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
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var StockbaseConfiguration
     */
    private $config;
    /**
     * @var ProductCollection
     */
    private $productCollection;
    /**
     * @var ProductModel
     */
    private $product;
    /**
     * @var UrlHelper
     */
    private $urlHelper;
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * File interface
     *
     * @var File
     */
    private $file;
    
    /**
     * Images constructor.
     * @param LoggerInterface $logger
     * @param ObjectManagerInterface $objectManager
     * @param StockbaseClientFactory $stockbaseClientFactory
     * @param StockbaseConfiguration $config
     * @param ProductCollection $productCollection
     * @param ProductModel $product
     * @param UrlHelper $urlHelper
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        StockbaseClientFactory $stockbaseClientFactory,
        StockbaseConfiguration $config,
        ProductCollection $productCollection,
        ProductModel $product,
        UrlHelper $urlHelper,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->logger = $logger;
        $this->stockbaseClientFactory = $stockbaseClientFactory;
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->productCollection = $productCollection;
        $this->product = $product;
        $this->urlHelper = $urlHelper;
        $this->directoryList = $directoryList;
        $this->file = $file;
    }

    /**
     * Executes the job.
     */
    public function execute()
    {
        // validate configuration:
        if (!$this->config->isModuleEnabled() || !$this->config->isImagesSyncEnabled()) {
            return;
        }
        // start process:
        $this->logger->info('Synchronizing Stockbase images...');
        // get all the eans:
        $allEans = $this->getAllEans();
        // get processed eans:
        $processedEans = json_decode($this->getImagesEans()) ?: array();
        // process 100 unprocessed eans at a time:
        $eans = array_slice(array_diff($allEans, $processedEans), 0, 100);
        try {
            $client = $this->stockbaseClientFactory->create();
            $images = $client->getImages($eans);
        } catch (Exception $e) {
            $this->logger->info('Cron runImageImport error: '.$e->getMessage());
            return false;
        }
        // download and save the images locally:
        $this->saveImageForProduct($images);
        // update the processed images configuration:
        $processedEans = array_merge($processedEans, $eans);
        $encodedEans = json_encode($processedEans);
        $this->saveImagesEans($encodedEans);
        $this->logger->info('Stockbase images synchronization complete.');
    }

    /**
     * @return array
     */
    private function getAllEans()
    {
        $this->logger->info('Get All EANs process');
        // get ean attribute:
        $attribute = $this->config->getEanFieldName();
        if($attribute) {
            // apply filters and paginate by 100:
            $collection = $this->productCollection->create()
                ->addAttributeToSelect($attribute)
                ->addAttributeToFilter($attribute, array('notnull' => true, 'neq' => '')) // not null and not empty.
                ->setPageSize(100);
            // iterate over the pages:
            $eans = array();
            $currentPage = 0;
            $lastPage = $collection->getLastPageNumber();
            while ($currentPage < $lastPage) {
                // load the data of this page in a single query:
                $collection->setCurPage(++$currentPage);
                $collection->load();
                // iterate over the products:
                foreach ($collection as $product) {
                    $ean = $product->getData($attribute);
                    if ($ean) {
                        // add the ean if this product has one:
                        $eans[] = $ean;
                    }
                }
            }
            $this->logger->info('Found EANs: '.count($eans));
            return $eans;
        } else {
            $this->logger->info('Please setup the EAN attribute.');
        }
    }

    /**
     * Saves images array from stockbase for given $ean
     *
     * @param array $images
     *
     * @return bool
     */
    private function saveImageForProduct($images)
    {
        $this->logger->info('Save images process: ');
        // get product model:
        $productModel = $this->product;
        // get ean attribute:
        $eanField = $this->config->getEanFieldName();
        // get client:
        $client = $this->stockbaseClientFactory->create();
        // loop images:
        foreach ($images as $image) {
            $this->logger->info($image->{'Url'});
            // load product by ean:
            $product = $productModel->loadByAttribute($eanField, $image->EAN);
            // continue looping if we do not have product:
            if (!$product) {
                continue;
            }
            // get image from stockbase:
            $protectedImage = $client->getImageFile($image->{'Url'});
            // create temporal folder if it is not exists:
            $tmpDir = $this->getMediaDirTmpDir();
            $this->file->checkAndCreateFolder($tmpDir);
            // get new file path:
            $newFileName = $tmpDir . baseName($image->{'Url'});
            // read file from URL and copy it to the new destination:
            $result = $this->file->read($protectedImage, $newFileName);
            if ($result) {
                if ($product->getMediaGallery() == null) {
                    $product->setMediaGallery(array('images' => array(), 'values' => array()));
                }
                // add saved file to the $product gallery:
                $product->addImageToMediaGallery(
                    $newFileName,
                    array('image', 'small_image', 'thumbnail'),
                    false,
                    false
                );
                // save product:
                $product->{'save'}();
                $this->logger->info('Saved.');
            }
        }
        return true;
    }

    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    private function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
    }

    /**
     * @return mixed
     */
    private function getImagesEans()
    {
        return $this->config->getImagesEans();
    }

    /**
     * @param $eans
     */
    private function saveImagesEans($eans)
    {
        $this->config->saveImagesEans($eans);
    }

}
