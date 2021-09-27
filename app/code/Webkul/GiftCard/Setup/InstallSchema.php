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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /*
         * Create table 'wk_gift'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_gift'))
            ->addColumn(
                'gift_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Gift ID'
            )
            ->addColumn(
                'price',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Price of Gift Coupan'
            )
            ->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Description'
            )
            ->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'email'
            )

            ->addColumn(
                'gift_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Gift Code'
            )
            ->addColumn(
                'from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'From'
            )->setComment('GiftCard Coupan Table');
        $installer->getConnection()->createTable($table);

        /*
         * Create table 'wk_giftUser'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('wk_giftuser'))
            ->addColumn(
                'giftuserid',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Gift User ID'
            )
            ->addColumn(
                'giftcodeid',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Id of gift code'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Code'
            )
            ->addColumn(
                'amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Amount'
            )
            ->addColumn(
                'alloted',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => true, 'nullable' => false,'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Alloted Time'
            )
            ->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'Email'
            )->addColumn(
                'from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => false],
                'From'
            )->addColumn(
                'remaining_amt',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Remaining amount'
            )->setComment('GiftCard Coupan Table');
            $installer->getConnection()->createTable($table);
            $installer->getConnection()->addIndex(
                $installer->getTable('wk_giftuser'),
                $installer->getIdxName(
                    'wk_giftuser',
                    'email',
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                'email',
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
    }
}
