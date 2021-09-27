<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\AutoRelated\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('mageplaza_autorelated_block_rule')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_autorelated_block_rule'))
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Rule Id')
                ->addColumn('name', Table::TYPE_TEXT, 255, [], 'Name')
                ->addColumn('block_type', Table::TYPE_TEXT, 255, [], 'Type')
                ->addColumn('from_date', Table::TYPE_DATE, null, ['nullable' => true, 'default' => null], 'From')
                ->addColumn('to_date', Table::TYPE_DATE, null, ['nullable' => true, 'default' => null], 'To')
                ->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Is Active'
                )
                ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Conditions Serialized')
                ->addColumn('actions_serialized', Table::TYPE_TEXT, '2M', [], 'Actions Serialized')
                ->addColumn(
                    'category_conditions_serialized',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Category Conditions Serialized'
                )
                ->addColumn(
                    'sort_order',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Sort Order'
                )
                ->addColumn(
                    'parent_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Parent Rule ID'
                )
                ->addColumn(
                    'impression',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Impression'
                )
                ->addColumn(
                    'click',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Click Count'
                )
                ->addColumn('location', Table::TYPE_TEXT, 255, [], 'Location')
                ->addColumn('block_name', Table::TYPE_TEXT, 255, [], 'Block Name')
                ->addColumn(
                    'product_layout',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Layout'
                )
                ->addColumn(
                    'limit_number',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Limit Number Of Products'
                )
                ->addColumn(
                    'display_out_of_stock',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Display Out Of Stock Products'
                )
                ->addColumn(
                    'product_layout',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Layout'
                )
                ->addColumn(
                    'sort_order_direction',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Sort Order Direction'
                )
                ->addColumn('display_additional', Table::TYPE_TEXT, 255, [], 'Display additional Information')
                ->addColumn('add_ruc_product', Table::TYPE_TEXT, 255, [], 'Add Related Up Sell Cross Cell Product')
                ->addColumn('product_not_displayed', Table::TYPE_TEXT, 255, [], 'Product is not displayed')
                ->addColumn(
                    'total_impression',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Total Impression'
                )
                ->addColumn(
                    'total_click',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Total Click Count'
                )
                ->addColumn('display', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Display')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Creation Time'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Update Time'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'mageplaza_autorelated_block_rule',
                        ['is_active', 'sort_order', 'to_date', 'from_date']
                    ),
                    ['is_active', 'sort_order', 'to_date', 'from_date']
                )
                ->setComment('Automatic Related Products Block Rule');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_autorelated_block_rule_store')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_autorelated_block_rule_store'))
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Rule Id')
                ->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Store Id'
                )
                ->addIndex($installer->getIdxName('mageplaza_autorelated_block_rule_store', ['store_id']), ['store_id'])
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_autorelated_block_rule_store',
                        'rule_id',
                        'mageplaza_autorelated_block_rule',
                        'rule_id'
                    ),
                    'rule_id',
                    $installer->getTable('mageplaza_autorelated_block_rule'),
                    'rule_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_autorelated_block_rule_store', 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Auto Related Product To Stores Relations');

            $installer->getConnection()->createTable($table);
        }

        $customerGroupTable  = $setup->getConnection()->describeTable($setup->getTable('customer_group'));
        $customerGroupIdType = $customerGroupTable['customer_group_id']['DATA_TYPE'] == 'int'
            ? Table::TYPE_INTEGER : $customerGroupTable['customer_group_id']['DATA_TYPE'];
        if (!$installer->tableExists('mageplaza_autorelated_block_rule_customer_group')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_autorelated_block_rule_customer_group'))
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Rule Id')
                ->addColumn(
                    'customer_group_id',
                    $customerGroupIdType,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                    'Customer Group Id'
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_autorelated_block_rule_customer_group', ['customer_group_id']),
                    ['customer_group_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_autorelated_block_rule_customer_group',
                        'rule_id',
                        'mageplaza_autorelated_block_rule',
                        'rule_id'
                    ),
                    'rule_id',
                    $installer->getTable('mageplaza_autorelated_block_rule'),
                    'rule_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_autorelated_block_rule_customer_group',
                        'customer_group_id',
                        'customer_group',
                        'customer_group_id'
                    ),
                    'customer_group_id',
                    $installer->getTable('customer_group'),
                    'customer_group_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Auto Related Product To Customer Groups Relations');
            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_autorelated_actions_index')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_autorelated_actions_index'))
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false
                ], 'Rule Id')
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'mageplaza_autorelated_actions_index',
                        ['rule_id', 'product_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['rule_id', 'product_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_autorelated_actions_index',
                        'rule_id',
                        'mageplaza_autorelated_block_rule',
                        'rule_id'
                    ),
                    'rule_id',
                    $installer->getTable('mageplaza_autorelated_block_rule'),
                    'rule_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_autorelated_actions_index',
                        'product_id',
                        'catalog_product_entity',
                        'product_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Auto Related Product Actions Index');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
