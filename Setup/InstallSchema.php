<?php


namespace Stockbase\Integration\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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
                    Table::TYPE_FLOAT,
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

        $tableName = $setup->getTable('stockbase_stock_reserve');
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->setComment('Stockbase stock reserve')
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'ean',
                    Table::TYPE_BIGINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'International/European Article Number'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Reserved amount'
                )
                ->addColumn(
                    'magento_stock_amount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Amount to subtract from Magento stock'
                )
                ->addColumn(
                    'quote_item_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Quote Item ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )
                ->addColumn(
                    'order_item_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Order Item ID'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Created at'
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['ean']),
                    ['ean'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['amount']),
                    ['amount'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['quote_item_id']),
                    ['quote_item_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['product_id']),
                    ['product_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['order_item_id']),
                    ['order_item_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                );

            $setup->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('stockbase_ordered_item');
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->setComment('Stockbase orders')
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'order_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Order Item ID'
                )
                ->addColumn(
                    'order_item_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Order Item ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )
                ->addColumn(
                    'ean',
                    Table::TYPE_BIGINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'International/European Article Number'
                )
                ->addColumn(
                    'amount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Reserved amount'
                )
                ->addColumn(
                    'stockbase_guid',
                    Table::TYPE_TEXT,
                    36,
                    ['nullable' => true],
                    'Stockbase GUID'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false],
                    'Created at'
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['order_id']),
                    ['order_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->addIndex(
                    $setup->getIdxName($tableName, ['order_item_id']),
                    ['order_item_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
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
                    $setup->getIdxName($tableName, ['amount']),
                    ['amount'],
                    ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                );

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
