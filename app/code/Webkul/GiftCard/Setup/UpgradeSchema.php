<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            // Get module table
            $giftDetails =  $setup->getTable('wk_gift');
            $giftUserDetails = $setup->getTable('wk_giftuser');

                // Check if the table already exists
            if ($setup->getConnection()->isTableExists($giftDetails) == true) {
                // Declare data
                $connection = $setup->getConnection();
                $connection->addColumn(
                    $giftDetails,
                    'message',
                    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'after' => 'from','comment'=>'Message in gift card ']
                );
                
                $connection->addColumn(
                    $giftDetails,
                    'duration',
                    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,'unsigned' => true,'after' => 'message',
                    'comment'=>'Active duration of gift card.']
                );
                $connection->addColumn(
                    $giftDetails,
                    'website_id',
                    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,'unsigned' => true,'after' => 'message',
                    'comment'=>'Website id of coupon.']
                );
                $connection->addColumn(
                    $giftDetails,
                    'order_id',
                    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,'unsigned' => true,'after' => 'duration',
                    'comment'=>'Order id of gift card product.']
                );
            }
            if ($setup->getConnection()->isTableExists($giftUserDetails) == true) {
                $connection->addColumn(
                    $giftUserDetails,
                    'is_active',
                    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'after' => 'remaining_amt','comment'=>'Is active gift card ?']
                );
                $connection->addColumn(
                    $giftUserDetails,
                    'is_expire',
                    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,'unsigned' => true,'default'=>0,
                    'after' => 'is_active','comment'=>'Is expired gift card ?']
                );
            }

            $quoteAddressTable = 'quote_address';
            $quoteTable = 'quote';
            $orderTable = 'sales_order';
            $invoiceTable = 'sales_invoice';
            $creditmemoTable = 'sales_creditmemo';

            //Setup two columns for quote, quote_address and order
            //Quote address tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quoteAddressTable),
                    'fee',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        '10,2',
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Fee'
                    ]
                );
        
            //Quote tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quoteTable),
                    'fee',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        '10,2',
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Fee'

                    ]
                );
            $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteTable),
                'gift_code',
                [
                    'type' =>\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' =>'Gift Code'
                ]
            );

            //Order tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'fee',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        '10,2',
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Fee'

                    ]
                );

            //Invoice tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($invoiceTable),
                    'fee',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        '10,2',
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Fee'

                    ]
                );
            //Credit memo tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($creditmemoTable),
                    'fee',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        '10,2',
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Fee'

                    ]
                );
        }
        $connection = $setup->getConnection();
        $giftDetails =  $setup->getTable('wk_gift');
        $connection->addColumn(
            $giftDetails,
            'website_ids',
            ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'after' => 'website_id','comment'=>'gift card in websites']
        );
        $setup->endSetup();
    }
}
