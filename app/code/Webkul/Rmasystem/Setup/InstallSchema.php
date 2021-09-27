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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_rma'))
            ->addColumn(
                'rma_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rma ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Order ID'
            )
            ->addColumn(
                'group',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Group'
            )
            ->addColumn(
                'increment_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Increment Id'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Customer ID'
            )
            ->addColumn(
                'package_condition',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Package Condition'
            )

            ->addColumn(
                'resolution_type',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Resolution Type'
            )
            ->addColumn(
                'additional_info',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Additional Info'
            )
            ->addColumn(
                'customer_delivery_status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Customer Delivery Status'
            )
            ->addColumn(
                'customer_consignment_no',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Customer Consignment No'
            )
            ->addColumn(
                'admin_delivery_status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Admin Delivery Status'
            )
            ->addColumn(
                'admin_consignment_no',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Admin Consignment No'
            )
            ->addColumn(
                'image',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Image'
            )
            ->addColumn(
                'shipping_label',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Shipping Label'
            )
            ->addColumn(
                'guest_email',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Guest email'
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Status'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Creation Time'
            )
            ->setComment('Rma Product Table');
        $installer->getConnection()->createTable($table);

         /**
         * Create table 'wk_rma_conversation'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_rma_conversation'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'rma_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Rma ID'
            )
            ->addColumn(
                'message',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Message'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [],
                'Creation Time'
            )
            ->addColumn(
                'sender',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Sender'
            )
            ->setComment('Rma Conversation Table');
        $installer->getConnection()->createTable($table);

         /**
         * Create table 'wk_rma_items'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_rma_items'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'rma_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Rma ID'
            )
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Item ID'
            )
            ->addColumn(
                'reason_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Reason ID'
            )
            ->addColumn(
                'qty',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Quantity'
            )
            ->setComment('Rma Item Table');
        $installer->getConnection()->createTable($table);
         /**
         * Create table 'wk_rma_label'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_rma_label'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Label Title'
            )
            ->addColumn(
                'filename',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'File Name'
            )
            ->addColumn(
                'price',
                Table::TYPE_DECIMAL,
                null,
                ['unsigned' => true, 'nullable' => false,'default' => 0],
                'Label price'
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Status'
            )
            ->setComment('Rma Label Table');
        $installer->getConnection()->createTable($table);

         /**
         * Create table 'wk_rma_reason'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_rma_reason'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'reason',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => null],
                'Reason'
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Status'
            )
        ->setComment('Rma Reason Table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
