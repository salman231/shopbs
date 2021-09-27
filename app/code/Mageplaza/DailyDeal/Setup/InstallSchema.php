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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\DailyDeal\Setup
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
        if (!$installer->tableExists('mageplaza_dailydeal_deal')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_dailydeal_deal'))
                ->addColumn('deal_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ], 'Deal Id')
                ->addColumn('product_id', Table::TYPE_INTEGER, null, [], 'Product Id')
                ->addColumn('product_name', Table::TYPE_TEXT, 255, [], 'Product Name')
                ->addColumn('product_sku', Table::TYPE_TEXT, 255, [], 'Product Sku')
                ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Status')
                ->addColumn('is_featured', Table::TYPE_SMALLINT, null, [
                    'nullable' => false,
                    'default'  => '0'
                ], 'Is Featured')
                ->addColumn('deal_price', Table::TYPE_DECIMAL, '12,4', [], 'Deal Price')
                ->addColumn('deal_qty', Table::TYPE_INTEGER, null, [], 'Deal Qty')
                ->addColumn('sale_qty', Table::TYPE_INTEGER, null, [], 'Sale Qty')
                ->addColumn('store_ids', Table::TYPE_TEXT, null, ['nullable' => false, 'unsigned' => true,], 'Store Id')
                ->addColumn('date_from', Table::TYPE_DATETIME, null, [], 'Date From')
                ->addColumn('date_to', Table::TYPE_DATETIME, null, [], 'Date To')
                ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT
                ], 'Creation Time')
                ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT_UPDATE
                ], 'Update Time')
                ->setComment('Daily Deal Block');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
