<?php

namespace Stockbase\Integration\Helper;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Catalog\Model\Product as ProductModel;
use Stockbase\Integration\Model\ProductImage;
use Stockbase\Integration\Model\ResourceModel\ProductImage as ProductImageResource;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;

/**
 *
 * Stockbase images helper.
 *
 * Class Images
 * @package Stockbase\Integration\Helper
 */
class Images
{

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var File
     */
    private $file;
    /**
     * @var ProductImage
     */
    private $productImage;
    /**
     * @var ProductImageResource
     */
    private $productImageResource;
    /**
     * @var ProductModel
     */
    private $product;
    /**
     * @var StockbaseConfiguration
     */
    private $config;

    /**
     * Images constructor.
     * @param LoggerInterface $logger
     * @param StockbaseConfiguration $config
     * @param ProductImage $productImage
     * @param ProductImageResource $productImageResource
     * @param ProductModel $product
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        LoggerInterface $logger,
        StockbaseConfiguration $config,
        ProductImage $productImage,
        ProductImageResource $productImageResource,
        ProductModel $product,
        DirectoryList $directoryList,
        File $file
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->productImage = $productImage;
        $this->productImageResource = $productImageResource;
        $this->product = $product;
        $this->directoryList = $directoryList;
        $this->file = $file;
    }

    /**
     * Saves images array from stockbase for given $ean
     *
     * @param array $images
     *
     * @return bool
     */
    public function saveProductImages($images)
    {
        $this->logger->info('Save images process:');
        $newImagesCount = 0;
        // get product model:
        $productModel = $this->product;
        // get ean attribute:
        $eanField = $this->config->getEanFieldName();
        // loop images:
        foreach ($images as $image) {
            $this->logger->info('Image URL: '.$image->{'Url'}.' - EAN: '.$image->EAN);
            // load product by ean:
            $product = $productModel->loadByAttribute($eanField, $image->EAN);
            // continue looping if we do not have product:
            if (!$product) {
                $this->logger->info('Product not found for EAN: '.$image->EAN);
                continue;
            }
            $this->logger->info('Loaded product: '.$product->getId());
            if(!$product->getData('stockbase_product')) {
                $this->logger->info('The Product is not mark as Stockbase product: '.$product->getId());
                continue;
            }
            // stockbase image:
            $stockbaseImage = (string)$image->{'Url'};
            // image name:
            $imageName = baseName($image->{'Url'});
            // check if the image exists:
            $imageCollection = $this->productImageResource->imageExists($imageName, $product->getId(), $image->EAN);
            if(count($imageCollection)>0) {
                $this->logger->info('The image '.$imageName.' is already synchronized for product '.$product->getId());
                continue;
            }
            // create temporal folder if it is not exists:
            $tmpDir = $this->getMediaDirTmpDir();
            $this->file->checkAndCreateFolder($tmpDir);
            // get new file path:
            $newFileName = $tmpDir . $imageName;
            // if the image file is not in our system:
            if(!file_exists($newFileName)) {
                // read file from URL and copy it to the new destination:
                $this->file->read($stockbaseImage, $newFileName);
                $this->logger->info('New image saved: '. $newFileName);
            }
            // if the process worked then the file should be there now:
            if (file_exists($newFileName)) {
                // if product gallery is empty the set the default values:
                $mediaGallery = $product->getMediaGallery();
                if (!$mediaGallery) {
                    $product->setMediaGallery(array('images' => array(), 'values' => array()));
                } else {
                    // if the product has a gallery and the image is already there then continue with the next one:
                    if(is_array($mediaGallery['images']) && in_array($imageName, $mediaGallery['images'])) {
                        continue;
                    }
                }
                // add saved file into the product gallery:
                $product->addImageToMediaGallery(
                    $newFileName,
                    array('image', 'small_image', 'thumbnail'),
                    false,
                    false
                );
                // save product:
                $product->save();
                // save image in relation table:
                $imageModel = clone($this->productImage);
                // image data:
                $imageData = [
                    'ean' => $image->EAN,
                    'product_id' => $product->getId(),
                    'image' => $imageName,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                // save:
                $imageModel->setData($imageData)->save();
                // end process.
                $this->logger->info('Product saved.');
                $newImagesCount++;
            } else {
                $this->logger->info('There is an issue with the image: '.$newFileName);
            }
        }
        return $newImagesCount;
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

}
