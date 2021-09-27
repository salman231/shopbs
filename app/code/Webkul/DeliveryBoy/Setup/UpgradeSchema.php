<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * @param  \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param  \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function upgrade(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $textType = \Magento\Framework\DB\Ddl\Table::TYPE_TEXT;
        $integerType = \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER;
        $timestampType = \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP;
        $timestampInit = \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT;
        $setup->startSetup();

        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            $setup->getConnection()->addColumn(
                $setup->getTable("deliveryboy_orders"),
                "assign_status",
                [
                    "type"     => $textType,
                    "length"   => 10,
                    "unsigned" => false,
                    "nullable" => false,
                    "default"  => "",
                    "comment"  => "Assigned Status"
                ]
            );
        }
        $setup->getConnection()->addColumn(
            $setup->getTable("deliveryboy_orders"),
            "picked",
            [
                "type"     => $integerType,
                "length"   => 10,
                "unsigned" => false,
                "nullable" => false,
                "default"  => "0",
                "comment"  => "Is Picked"
            ]
        );

        // create deliveryboy_comments table //////////////////////////////////////////////////////
        $table = $setup->getConnection()
            ->newTable(
                $setup->getTable("deliveryboy_comments")
            )->addColumn(
                "id",
                $integerType,
                null,
                ["identity" => true, "unsigned" => true, "nullable" => false, "primary" => true],
                "Id"
            )->addColumn(
                "sender_id",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Sender Id"
            )->addColumn(
                "order_increment_id",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Order Increment Id"
            )->addColumn(
                "deliveryboy_order_id",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Deliveryboy Order Id"
            )->addColumn(
                "is_deliveryboy",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Is Deliveryboy"
            )->addColumn(
                "comment",
                $textType,
                null,
                ["nullable" => false,"default" => ""],
                "Comment"
            )->addColumn(
                "commented_by",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Comment By"
            )->addColumn(
                "created_at",
                $timestampType,
                null,
                ["nullable" => false, "default" => $timestampInit],
                "Creation Date Time"
            )->setComment(
                "DeliveryBoy Comments Table"
            );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
