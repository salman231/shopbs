<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_methods_payment')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Website ID'
        )->addColumn(
            'method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Method'
        )->addColumn(
            'group_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Group Ids'
        )->addForeignKey(
            $installer->getFkName('amasty_methods_payment', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Amasty Methods Payment'
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_methods_shipping')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Website ID'
        )->addColumn(
            'method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Method'
        )->addColumn(
            'group_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Group Ids'
        )->addForeignKey(
            $installer->getFkName('amasty_methods_shipping', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Amasty Methods Shipping'
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}