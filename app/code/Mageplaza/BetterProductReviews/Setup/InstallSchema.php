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
 * @category  Mageplaza
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 *
 * @package Mageplaza\BetterProductReviews\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     * @SuppressWarnings(Unused)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if ($installer->tableExists('review_detail')) {
            $columns = [
                'mp_bpr_images' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => '2M',
                    'comment' => 'Mageplaza BPR Review Images (Json)'
                ],
                'mp_bpr_recommended_product' => [
                    'type' => Table::TYPE_SMALLINT,
                    'length' => 1,
                    'nullable' => true,
                    'comment' => 'Mageplaza BPR Recommended Product'
                ],
                'mp_bpr_verified_buyer' => [
                    'type' => Table::TYPE_SMALLINT,
                    'length' => 1,
                    'nullable' => true,
                    'comment' => 'Mageplaza BPR Verified Buyer'
                ],
                'mp_bpr_helpful' => [
                    'type' => Table::TYPE_INTEGER,
                    'length' => null,
                    'default' => '0',
                    'comment' => 'Mageplaza BPR Helpful'
                ]
            ];

            $userTable = $installer->getTable('review_detail');
            foreach ($columns as $name => $definition) {
                $connection->addColumn($userTable, $name, $definition);
            }
        }

        if (!$installer->tableExists('mageplaza_betterproductreviews_review_reply')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_betterproductreviews_review_reply'))
                ->addColumn('reply_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ], 'Reply ID')
                ->addColumn('review_id', Table::TYPE_BIGINT, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => '0'
                ], 'Review id')
                ->addColumn('user_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'User ID')
                ->addColumn('reply_enabled', Table::TYPE_INTEGER, 1, [], 'Reply Enabled')
                ->addColumn('reply_nickname', Table::TYPE_TEXT, 255, ['nullable => false'], 'Reply Nickname')
                ->addColumn('reply_content', Table::TYPE_TEXT, '64k', [], 'Reply Content')
                ->addColumn('reply_updated_at', Table::TYPE_TIMESTAMP, null, [], 'Reply Updated At')
                ->addColumn('reply_created_at', Table::TYPE_TIMESTAMP, null, [], 'Reply Created At')
                ->addIndex(
                    $installer->getIdxName(
                        'mageplaza_betterproductreviews_review_reply',
                        ['review_id']
                    ),
                    ['review_id']
                )
                ->addIndex(
                    $installer->getIdxName(
                        'mageplaza_betterproductreviews_review_reply',
                        ['user_id']
                    ),
                    ['user_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_betterproductreviews_review_reply',
                        'review_id',
                        'review',
                        'review_id'
                    ),
                    'review_id',
                    $installer->getTable('review'),
                    'review_id',
                    Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_betterproductreviews_review_reply',
                        'user_id',
                        'admin_user',
                        'user_id'
                    ),
                    'user_id',
                    $installer->getTable('admin_user'),
                    'user_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Review Admin Reply Information');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
