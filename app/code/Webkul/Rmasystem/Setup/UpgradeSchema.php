<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Update tables 'wk_rma_items'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('wk_rma_items'),
            'order_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Order Id'
            ]
        );

        /**
         * Update tables 'wk_rma'
         */
        $setup->getConnection()->addColumn(
            $setup->getTable('wk_rma'),
            'name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => null,
                'comment' => 'Customer Name'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('wk_rma'),
            'admin_status',
            [
              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
              'unsigned' => true,
              'nullable' => false,
              'default' => '0',
              'comment' => 'Admin Final Status'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('wk_rma'),
            'final_status',
            [
              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
              'unsigned' => true,
              'nullable' => false,
              'default' => '0',
              'comment' => 'Final Status'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('wk_rma_items'),
            'rma_reason',
            [
              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              'nullable' => false,
              'default' => null,
              'comment' => 'Rma Reason'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('wk_rma_conversation'),
            'attachment',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => null,
                'comment' => 'Attachment'
            ]
        );

        /**
         * Add foreign keys
         */
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'wk_rma_items',
                'rma_id',
                'wk_rma',
                'rma_id'
            ),
            $setup->getTable('wk_rma_items'),
            'rma_id',
            $setup->getTable('wk_rma'),
            'rma_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'rma_conversation',
                'rma_id',
                'wk_rma',
                'rma_id'
            ),
            $setup->getTable('wk_rma_conversation'),
            'rma_id',
            $setup->getTable('wk_rma'),
            'rma_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        
        /**
         * create table wk_rma_customfield
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_rma_customfield'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'input_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'input type'
            )
            ->addColumn(
                'label',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'label'
            )
            ->addColumn(
                'inputname',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'inputname'
            )
            ->addColumn(
                'select_option',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'select_option'
            )
            ->addColumn(
                'validationtype',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'validationtype'
            )
            ->addColumn(
                'required',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'required'
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'status'
            )
            ->addColumn(
                'sort',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'sort'
            )
            ->setComment('Rma Custom field Table');
        $installer->getConnection()->createTable($table);

        /**
         * create table wk_rma_customfield_value
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_rma_customfield_value'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'field_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'field id'
            )
            ->addColumn(
                'rma_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'rma id'
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => null],
                'value'
            )
        ->setComment('Rma Custom field Values Table');
        $installer->getConnection()->createTable($table);

        $setup->getConnection()->changeColumn(
            $setup->getTable('wk_rma_label'),
            'price',
            'price',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '10,2',
                'comment' => 'Label price'
            ]
        );

        $installer->endSetup();
    }
}
