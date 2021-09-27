<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (!$setup->tableExists('sdcp_product_enabled') || version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addProductEnabledTable($setup);
        }

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addAjaxDetailColumn($setup);
        }
        if (!$setup->tableExists('sdcp_custom_url') || version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->addCustomUrlTable($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addProductEnabledTable($setup)
    {
        if (!$setup->tableExists('sdcp_product_enabled')) {
            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('sdcp_product_enabled')
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false],
                    'Enabled'
                )->addColumn(
                    'is_ajax_load',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    6,
                    ['nullable' => false],
                    'Enabled ajax load'
                )->addIndex(
                    $setup->getIdxName('sdcp_product_enabled', ['product_id']),
                    ['product_id']
                )
                ->setComment(
                    'Preselect key for configurable product'
                );
            $setup->getConnection()->createTable($table);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addAjaxDetailColumn($setup)
    {
        $bssSdcpTable = $setup->getTable('sdcp_product_enabled');
        $connection = $setup->getConnection();
        if ($connection->isTableExists($bssSdcpTable)
            && $connection->tableColumnExists($bssSdcpTable, 'is_ajax_load') === false
        ) {
            $connection->addColumn(
                $bssSdcpTable,
                'is_ajax_load',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => 6,
                    'nullable' => false,
                    'comment' => 'enable ajax load'
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addCustomUrlTable($setup)
    {
        if (!$setup->tableExists('sdcp_custom_url')) {
            $table = $setup->getConnection()
                ->newTable(
                    $setup->getTable('sdcp_custom_url')
                )
                ->addColumn(
                    'url_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['primary' => true, 'auto_increment' => true, 'nullable' => false],
                    'Key ID'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'custom_url',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Custom Url'
                )
                ->addColumn(
                    'parent_url',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Parent Url'
                )->addIndex(
                    $setup->getIdxName('sdcp_custom_url', ['product_id']),
                    ['product_id']
                )
                ->setComment(
                    'Preselect key for configurable product'
                );
            $setup->getConnection()->createTable($table);
        }
    }
}
