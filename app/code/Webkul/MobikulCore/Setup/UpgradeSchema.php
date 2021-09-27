<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class Upgrade Schema to upgrade database
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $textType = \Magento\Framework\DB\Ddl\Table::TYPE_TEXT;
        $integerType = \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER;
        $decimalType = \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL;
        $timestampType = \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP;
        $timestampInit = \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT;
        $setup->startSetup();
        $setup->getConnection()->addColumn(
            $setup->getTable("mobikul_devicetoken"),
            "email",
            [
                "type" => $textType,
                "unsigned" => true,
                "nullable" => true,
                "default" => null,
                "comment" => "Email for guest user"
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable("mobikul_devicetoken"),
            "os",
            [
                "type" => $textType,
                "unsigned" => true,
                "nullable" => true,
                "default" => null,
                "comment" => "Operating System"
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable("mobikul_categoryimages"),
            "store_id",
            [
                "type" => $textType,
                "unsigned" => true,
                "nullable" => false,
                "default" => null,
                "comment" => "Store Id"
            ]
        );

        $table = $setup->getConnection()
            ->newTable($setup->getTable("mobikul_sales_order"))
            ->addColumn("order_id", $textType, null, ["nullable"=>true, "default"=>null], "Order Id")
            ->addColumn("real_order_id", $textType, null, ["nullable"=>true, "default"=>null], "Real Order Id")
            ->addColumn(
                "store_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Store Id"
            )
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true],
                "Id"
            )
            ->addColumn(
                "customer_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Customer Id"
            )
            ->addColumn("customer_name", $textType, null, ["nullable"=>false, "default"=>""], "Customer Name")
            ->addColumn(
                "created_at",
                $timestampType,
                null,
                ["nullable"=>false, "default"=>$timestampInit],
                "Creation Date Time"
            )
            ->addColumn("order_total", $decimalType, "12,4", [], "Order Total")
            ->setComment("Mobikul Sales Table");
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()
            ->newTable($setup->getTable("mobikul_carouselimage"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true],
                "Id"
            )
            ->addColumn("filename", $textType, null, ["nullable"=>true, "default"=>null], "File Name")
            ->addColumn("type", $textType, 255, ["nullable"=>true, "default"=>null], "Type")
            ->addColumn("title", $textType, 255, ["nullable"=>true, "default"=>null], "Title")
            ->addColumn(
                "pro_cat_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Product Category Id"
            )
            ->addColumn("status", $integerType, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Status")
            ->setComment("Mobikul Carousel Image Table");
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()
            ->newTable($setup->getTable("mobikul_carousel"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true],
                "Id"
            )
            ->addColumn("title", $textType, 255, ["nullable"=>true, "default"=>null], "Title")
            ->addColumn("type", $textType, 255, ["nullable"=>true, "default"=>null], "Type")
            ->addColumn("filename", $textType, null, ["nullable"=>true, "default"=>null], "Background Image")
            ->addColumn("color_code", $textType, null, ["nullable"=>true, "default"=>null], "Color Code")
            ->addColumn("images", $textType, 255, ["nullable"=>true, "default"=>null], "Selected Images")
            ->addColumn("status", $integerType, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Status")
            ->addColumn(
                "sort_order",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Sort Order"
            )
            ->addColumn("image_ids", $textType, 255, ["nullable"=>true, "default"=>null], "Selected Image")
            ->addColumn("product_ids", $textType, 255, ["nullable"=>true, "default"=>null], "Selected Products")
            ->setComment("Mobikul Carousel Table");
        $setup->getConnection()->createTable($table);

        $setup->getConnection()->addColumn(
            $setup->getTable("mobikul_carousel"),
            "store_id",
            [
                "type" => $textType,
                "unsigned" => true,
                "nullable" => false,
                "default" => null,
                "comment" => "Store Id"
            ]
        );

        $table = $setup->getConnection()
            ->newTable($setup->getTable("mobikul_cache"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true],
                "Id"
            )
            ->addColumn("request_tag", $textType, 255, ["nullable"=>true, "default"=>null], "Request tag")
            ->addColumn("e_tag", $textType, 255, ["nullable"=>true, "default"=>null], "E tag")
            ->addColumn(
                "counter",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Counter for caching"
            )
            ->setComment("Mobikul Cache Table");
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
        if (version_compare($context->getVersion(), '3.0.7', '<')) {
            $appcreator = $setup->getTable('mobikul_appcreator');
            if ($setup->getConnection()->isTableExists($appcreator) != true) {
                $tableAppCreator = $setup->getConnection()
                    ->newTable($appcreator)
                     ->addColumn(
                         'id',
                         \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                         10,
                         ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                         'Id'
                     )
                ->addColumn(
                    'layout_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Status'
                )->addColumn(
                    'label',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Label'
                )->addColumn(
                    'position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Position'
                )->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Type'
                )
                ->setComment('App Creator Table Data')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
                $setup->getConnection()->createTable($tableAppCreator);
                $setup->endSetup();
            }
        }
        if (version_compare($context->getVersion(), '3.0.8', '<')) {
            $orderPurchasePoint = $setup->getTable('mobikul_orderPurchasePoint');
            if ($setup->getConnection()->isTableExists($orderPurchasePoint) != true) {
                $tableOrderPurchasePoint = $setup->getConnection()
                    ->newTable($orderPurchasePoint)
                     ->addColumn(
                         'entity_id',
                         \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                         10,
                         ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                         'Id'
                     )
                ->addColumn(
                    'increment_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Increment id'
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false, 'unsigned' => true],
                    'OrderId'
                )->addColumn(
                    'purchase_point',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'default' => ''],
                    'Purchase Point'
                )
                ->setComment('Order Purchase Table Data')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
                $setup->getConnection()->createTable($tableOrderPurchasePoint);
                $setup->endSetup();
            }
        }
    }
}
