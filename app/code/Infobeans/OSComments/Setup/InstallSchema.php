<?php
/**
 * InfoBeans Sales Shipment Comment Extension
 *
 * @category   Infobeans
 * @package    Infobeans_OSComments
 * @version    2.0.0
 * @description Override template to show Pre-Order Note
 *
 * Release with version 2.0.0
 *
 * @author     InfoBeans Technologies Limited http://www.infobeans.com/
 * @copyright  Copyright (c) 2017 InfoBeans Technologies Limited
 */
namespace Infobeans\OSComments\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    // @codingStandardsIgnoreLine
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        
        // Add column in quote table
        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'delivery_comment',
            [
                'type' => 'text',
                'nullable' => true,
                'comment' => 'Delivery Comment'
            ]
        );

        // Add column in sales order table
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'delivery_comment',
            [
                'type' => 'text',
                'nullable' => true,
                'comment' => 'Delivery Comment',
            ]
        );
        
        // Add column in quote address table for multi-checkout
        $installer->getConnection()->addColumn(
            $installer->getTable('quote_address'),
            'delivery_comment',
            [
                'type' => 'text',
                'nullable' => true,
                'comment' => 'Delivery Comment',
            ]
        );
        $setup->endSetup();
    }
}
