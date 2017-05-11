<?php


namespace Strategery\Stockbase\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @author Vitaly Zilnik <vitaly@strategery.io>
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tableName = $setup->getTable('stockbase_stock');
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->setComment('Stockbase stock index')
                ->addColumn(
                    'ean',
                    Table::TYPE_BIGINT,
                    null,
                    ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'International/European Article Number'
                )
                ->addColumn(
                    'brand',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Brand of the supplier'
                )
                ->addColumn(
                    'code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Unique Stockbase code set for a brand'
                )
                ->addColumn(
                    'supplier_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Unique supplier code set for a supplier'
                )
                ->addColumn(
                    'supplier_gln',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'GLN of the supplier'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Available stock amount for sale'
                )
                ->addColumn(
                    'noos',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Never Out Of Stock'
                )
                ->addColumn(
                    'timestamp',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Latest mutation timestamp'
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['amount']),
                    ['amount'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['noos']),
                    ['noos'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['timestamp']),
                    ['timestamp'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                );

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
