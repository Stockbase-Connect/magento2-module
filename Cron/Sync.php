<?php

namespace Stockbase\Integration\Cron;

use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;
use Stockbase\Integration\Model\ResourceModel\StockItem as StockItemResource;
use Stockbase\Integration\StockbaseApi\Client\StockbaseClientFactory;

/**
 * Stockbase stock synchronization cron job.
 */
class Sync
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
     * Sync constructor.
     * @param LoggerInterface        $logger
     * @param ObjectManagerInterface $objectManager
     * @param StockbaseClientFactory $stockbaseClientFactory
     * @param StockbaseConfiguration $config
     */
    public function __construct(
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        StockbaseClientFactory $stockbaseClientFactory,
        StockbaseConfiguration $config
    ) {
        $this->logger = $logger;
        $this->stockbaseClientFactory = $stockbaseClientFactory;
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * Executes the job.
     */
    public function execute()
    {
        if (!$this->config->isModuleEnabled()) {
            return;
        }

        $this->logger->info("Synchronizing Stockbase stock index...");

        $client = $this->stockbaseClientFactory->create();

        /** @var StockItemResource $stockItemResource */
        $stockItemResource = $this->objectManager->create(StockItemResource::class);

        //$lastModifiedDate = $stockItemResource->getLastModifiedItemDate();
        $lastModifiedDate = null; // Disabled due to unstable API

        $this->logger->info(
            sprintf(
                "Downloading Stockbase stock data since %s...",
                $lastModifiedDate !== null ? $lastModifiedDate->format('Y-m-d H:i:s') : 'the beginning'
            )
        );
        $stock = $client->getStock($lastModifiedDate);

        $this->logger->info("Updating local index...");
        $total = $stockItemResource->updateFromStockObject($stock);

        if ($total > 0) {
            $this->logger->info("{$total} Stockbase items updated.");
        } else {
            $this->logger->info("No new updates were found.");
        }

        $this->logger->info("Stockbase stock index synchronization complete.");
    }
}
