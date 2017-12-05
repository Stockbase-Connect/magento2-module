<?php

namespace Stockbase\Integration\Model\Observer;

use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClientFactory;
use Stockbase\Integration\Helper\Images as ImagesHelper;

/**
 * Class ProductSaveAfterObserver
 */
class ProductSaveAfterObserver implements ObserverInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var StockbaseConfiguration
     */
    private $config;
    /**
     * @var ImagesHelper
     */
    private $imagesHelper;
    /**
     * @var StockbaseClientFactory
     */
    private $stockbaseClientFactory;

    /**
     * ProductSaveAfterObserver constructor.
     * @param LoggerInterface $logger
     * @param StockbaseConfiguration $config
     * @param ImagesHelper $imagesHelper
     * @param StockbaseClientFactory $stockbaseClientFactory
     */
    public function __construct(
        LoggerInterface $logger,
        StockbaseConfiguration $config,
        ImagesHelper $imagesHelper,
        StockbaseClientFactory $stockbaseClientFactory
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->imagesHelper = $imagesHelper;
        $this->stockbaseClientFactory = $stockbaseClientFactory;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();
        // validate configuration:
        if (!$this->config->isModuleEnabled() || !$this->config->isImagesSyncEnabled()) {
            return;
        }
        // get ean attribute:
        $attribute = $this->config->getEanFieldName();
        // validate attribute:
        if($attribute) {
            // get ean:
            $ean = $product->getData($attribute);
            // if the ean is not empty:
            if ($ean) {
                $this->logger->info('Image Sync in Product Save process. EAN found: '.$ean);
                $client = $this->stockbaseClientFactory->create();
                $images = $client->getImages([$ean]);
                // validate returned images:
                if(is_array($images->{'Items'}) && count($images->{'Items'}) > 0) {
                    // download and save the images locally:
                    $this->imagesHelper->saveProductImages($images->{'Items'});
                    $this->logger->info('New images synchronized.');
                }
            }
        }
    }

}
