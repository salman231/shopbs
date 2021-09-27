<?php
/**
 * Productkeys Productkeys Db Table.
 * @category  Dart
 * @package   Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Get dart_productkeys table
        $tableName = $installer->getTable('dart_productkeys');

        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create dart_productkeys table
            $table = $installer->getConnection()
                        ->newTable($tableName)
                        ->addColumn(
                            'id',
                            Table::TYPE_INTEGER,
                            null,
                            [
                                'identity' => true,
                                'unsigned' => true,
                                'nullable' => false,
                                'primary' => true
                            ],
                            'ID'
                        )
                        ->addColumn(
                            'sku',
                            Table::TYPE_TEXT,
                            null,
                            ['nullable' => false, 'default' => ''],
                            'SKU (Productkey Pool)'
                        )
                        ->addColumn(
                            'product_key',
                            Table::TYPE_TEXT,
                            null,
                            ['nullable' => false, 'default' => ''],
                            'Product Key'
                        )
                        ->addColumn(
                            'status',
                            Table::TYPE_INTEGER,
                            1,
                            ['nullable' => false, 'default' => '0'],
                            'Usage Staus'
                        )
                        ->addColumn(
                            'orderinc_id',
                            Table::TYPE_TEXT,
                            null,
                            ['nullable' => true, 'default' => ''],
                            'Order Id'
                        )
                        ->addColumn(
                            'created_at',
                            Table::TYPE_DATETIME,
                            null,
                            ['nullable' => false],
                            'Created At'
                        )
                        ->addColumn(
                            'updated_at',
                            Table::TYPE_DATETIME,
                            null,
                            ['nullable' => false],
                            'Updated At'
                        )
                ->setComment('Product Keys');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
