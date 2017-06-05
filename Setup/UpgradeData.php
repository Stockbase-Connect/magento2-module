<?php


namespace Stockbase\Integration\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Config\Model\ResourceModel\Config as ConfigResourceModel;
use Stockbase\Integration\Model\Config\StockbaseConfiguration;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;
    
    /**
     * @var ConfigResourceModel
     */
    private $configResource;
    
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * UpgradeData constructor.
     * @param CategorySetupFactory $categorySetupFactory
     * @param ScopeConfigInterface $config
     * @param ConfigResourceModel  $configResource
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        ScopeConfigInterface $config,
        ConfigResourceModel $configResource
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->configResource = $configResource;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$this->config->getValue(StockbaseConfiguration::CONFIG_ORDER_PREFIX)) {
            $this->configResource->saveConfig(
                StockbaseConfiguration::CONFIG_ORDER_PREFIX,
                uniqid('MAGE-'),
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
        
        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
            
            $categorySetup->addAttribute($entityTypeId, 'stockbase_product', [
                'type' => 'int',
                'label' => 'Stockbase product',
                'input' => 'boolean',
                'required' => false,
                'visible_on_front' => false,
                'apply_to' => 'simple',
                'unique' => false,
                'group' => 'Stockbase',
            ]);
        }
    }
}
