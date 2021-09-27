<?php
/**
 * Productkeys Productkeys Order Db Table Update.
 * @category  Dart
 * @package   Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            // Get module table
            $tableName = $setup->getTable('sales_order_item');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // Declare data
                $columns = [
                    'product_key_type' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Product Key Type'
                    ],
                    'product_keys' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Product Keys'
                    ],
                    'product_key_ids' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Product Key Ids'
                    ],
                    'product_keys_issued' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => '0',
                        'comment' => 'Product Key Status'
                    ],
                    'product_key_pool' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Product Key Pool Name'
                    ],
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }

            $newcolumn = $setup->getTable('dart_productkeys');
            if ($setup->getConnection()->isTableExists($newcolumn) == true) {
                $setup->getConnection()->addColumn($setup->getTable('dart_productkeys'), 'type', [
                    'type' => \Magento\Framework\Db\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'after' => 'id',
                    'comment' => 'Related Product Name'
                ]);
            }
        }
        $setup->endSetup();
    }
}
