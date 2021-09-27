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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mageplaza\AutoRelated\Model\Config\Source\DisplayMode;

/**
 * Class UpgradeSchema
 * @package Mageplaza\AutoRelated\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            if ($installer->tableExists('mageplaza_autorelated_block_rule')) {
                $installer->getConnection()->addColumn(
                    $installer->getTable('mageplaza_autorelated_block_rule'),
                    'display_mode',
                    [
                        'type'    => Table::TYPE_SMALLINT,
                        'default' => DisplayMode::TYPE_BLOCK,
                        'comment' => 'Display type ajax or block'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $installer->getTable('mageplaza_autorelated_block_rule'),
                    'add_ruc_product',
                    [
                        'type'    => Table::TYPE_TEXT,
                        'size'    => 255,
                        'comment' => 'Add Related Up Sell Cross Sell Product'
                    ]
                );

                $installer->getConnection()->addColumn(
                    $installer->getTable('mageplaza_autorelated_block_rule'),
                    'product_not_displayed',
                    [
                        'type'    => Table::TYPE_TEXT,
                        'size'    => 255,
                        'comment' => 'Product is not displayed'
                    ]
                );

                $installer->getConnection()->dropColumn(
                    $installer->getTable('mageplaza_autorelated_block_rule'),
                    'display'
                );
            }
        }

        $installer->endSetup();
    }
}
