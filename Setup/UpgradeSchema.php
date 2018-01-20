<?php

namespace Stockbase\Integration\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            $tableName = $setup->getTable('stockbase_product_images');
            if (!$setup->getConnection()->isTableExists($tableName)) {
                $table = $setup->getConnection()
                    ->newTable($tableName)
                    ->setComment('Stockbase product images')
                    ->addColumn(
                        'id',
                        Table::TYPE_BIGINT,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Image ID'
                    )
                    ->addColumn(
                        'ean',
                        Table::TYPE_BIGINT,
                        null,
                        ['identity' => false, 'unsigned' => true, 'nullable' => false],
                        'International/European Article Number'
                    )
                    ->addColumn(
                        'product_id',
                        Table::TYPE_BIGINT,
                        null,
                        ['identity' => false, 'unsigned' => true, 'nullable' => false],
                        'Magento Product ID'
                    )
                    ->addColumn(
                        'image',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => false],
                        'Image'
                    )
                    ->addColumn(
                        'timestamp',
                        Table::TYPE_DATETIME,
                        null,
                        ['nullable' => false],
                        'Latest Sync'
                    )
                    ->addIndex(
                        $setup->getIdxName($tableName, ['product_id']),
                        ['product_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                    )
                    ->addIndex(
                        $setup->getIdxName($tableName, ['ean']),
                        ['ean'],
                        ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                    )
                    ->addIndex(
                        $setup->getIdxName($tableName, ['timestamp']),
                        ['timestamp'],
                        ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                    );
                $setup->getConnection()->createTable($table);
            }
        }
    }
}
