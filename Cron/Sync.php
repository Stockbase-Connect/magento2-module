<?php

namespace Strategery\Stockbase\Cron;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;
use Strategery\Stockbase\Api\Client\StockbaseClientFactory;
use Strategery\Stockbase\Model\Config\StockbaseConfiguration;
use Strategery\Stockbase\Model\ResourceModel\StockItem as StockItemResource;

class Sync
{
    const LAST_MUTATION_TIMESTAMP_CACHE_KEY = 'stockbase_stock_last_mutation_timestamp';
    
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
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var StockbaseConfiguration
     */
    private $config;

    public function __construct(
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        CacheInterface $cache,
        StockbaseClientFactory $stockbaseClientFactory,
        StockbaseConfiguration $config
    )
    {
        $this->logger = $logger;
        $this->stockbaseClientFactory = $stockbaseClientFactory;
        $this->objectManager = $objectManager;
        $this->cache = $cache;
        $this->config = $config;
    }

    public function execute()
    {
        if (!$this->config->isModuleEnabled())
            return;
        
        $this->logger->info("Synchronizing Stockbase stock index...");
        
        $client = $this->stockbaseClientFactory->create();
        
        /** @var StockItemResource $stockItemResource */
        $stockItemResource = $this->objectManager->create(StockItemResource::class);
        
        $lastSyncDate = $this->cache->load(self::LAST_MUTATION_TIMESTAMP_CACHE_KEY);
        if ($lastSyncDate !== false) {
            $lastSyncDate = \DateTime::createFromFormat('U', $lastSyncDate);
        } else {
            $lastSyncDate = null;
        }

        $this->logger->info(sprintf(
            "Downloading Stockbase stock data since %s...",
            $lastSyncDate !== null ? $lastSyncDate->format('Y-m-d H:i:s') : 'the beginning'
        ));
        
        $stock = $client->getStock($lastSyncDate);

        $this->logger->info("Updating local index...");
        $data = [];
        $latestTimestamp = null;
        $count = 0;
        foreach ($stock->Groups as $group) {
            foreach ($group->Items as $item) {
                $count++;
                $data[] = [
                    'ean' => $item->EAN,
                    'brand' => isset($group->Brand) ? $group->Brand : null,
                    'code' => isset($group->Code) ? $group->Code : null,
                    'supplier_code' => isset($group->SupplierCode) ? $group->SupplierCode : null,
                    'supplier_gln' => !empty($group->SupplierGLN) ? $group->SupplierGLN : null,
                    'amount' => $item->Amount,
                    'noos' => $item->NOOS,
                    'timestamp' => date('Y-m-d H:i:s', $item->Timestamp),
                ];
                //var_dump($data);
                if (count($data) >= 100) {
                    $stockItemResource->bulkUpdate($data);
                    $data = [];
                }
                if ($item->Timestamp > $latestTimestamp) {
                    $latestTimestamp = (int)$item->Timestamp;
                }
            }
        }
        if (count($data) > 0) {
            $stockItemResource->bulkUpdate($data);
        }
        
        if ($latestTimestamp !== null) {
            $this->logger->info(sprintf(
                "%s Stockbase items updated. Last stock item mutation time: %s",
                $count,
                date('Y-m-d H:i:s', $latestTimestamp)
            ));
            $this->cache->save($latestTimestamp, self::LAST_MUTATION_TIMESTAMP_CACHE_KEY);
            
        } else {
            $this->logger->info("No new updated was found.");
        }

        $this->logger->info("Stockbase stock index synchronization complete.");
    }
}
