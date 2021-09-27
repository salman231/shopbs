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
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * InstallSchema Data Class
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $textType = \Magento\Framework\DB\Ddl\Table::TYPE_TEXT;
        $integerType = \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER;
        $timestampType = \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP;
        $timestampInit = \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT;
        $timestampInitUpdate = \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE;

        // Mobikul Banner Image Table ///////////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable($installer->getTable("mobikul_bannerimage"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true],
                "Id"
            )
            ->addColumn("filename", $textType, null, ["nullable"=>true, "default"=>null], "File Name")
            ->addColumn("status", $integerType, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Status")
            ->addColumn("type", $textType, 255, ["nullable"=>true, "default"=>null], "Type")
            ->addColumn(
                "pro_cat_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Product Category Id"
            )
            ->addColumn("store_id", $textType, 255, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Store ID")
            ->addColumn(
                "sort_order",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Sort Order"
            )
            ->setComment("Mobikul Banner Table");
        $installer->getConnection()->createTable($table);

        // Mobikul Notification Table ///////////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable($installer->getTable("mobikul_notification"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true],
                "Id"
            )
            ->addColumn("title", $textType, 255, ["nullable"=>true, "default"=>null], "Title")
            ->addColumn("content", $textType, null, ["nullable"=>true, "default"=>null], "Content")
            ->addColumn("type", $textType, 255, ["nullable"=>true, "default"=>null], "Type")
            ->addColumn("filename", $textType, 255, ["nullable"=>true, "default"=>null], "File Name")
            ->addColumn("collection_type", $textType, null, ["nullable"=>true, "default"=>null], "Collection Type")
            ->addColumn(
                "filter_data",
                $textType,
                null,
                ["nullable"=>true, "default"=>null],
                "Filter Data"
            )
            ->addColumn(
                "pro_cat_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Product Category Id"
            )
            ->addColumn("store_id", $textType, 255, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Store ID")
            ->addColumn("status", $integerType, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Status")
            ->addColumn(
                "created_at",
                $timestampType,
                null,
                ["nullable"=>false, "default"=>$timestampInit],
                "Creation Time"
            )
            ->addColumn(
                "updated_at",
                $timestampType,
                null,
                ["nullable"=>false, "default"=>$timestampInitUpdate],
                "Update Time"
            )
            ->setComment("Mobikul Notification Table");
        $installer->getConnection()->createTable($table);

        // Mobikul Featured Category Table //////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable($installer->getTable("mobikul_featuredcategories"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true],
                "ID"
            )
            ->addColumn("filename", $textType, null, ["nullable"=>true, "default"=>null], "File Name")
            ->addColumn(
                "category_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Product Category Id"
            )
            ->addColumn("store_id", $textType, 255, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Store Id")
            ->addColumn(
                "sort_order",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Sort Order"
            )
            ->addColumn("status", $integerType, null, ["unsigned"=>true, "nullable"=>false, "default"=>"0"], "Status")
            ->setComment("Mobikul Featured Category Table");
        $installer->getConnection()->createTable($table);

        // Mobikul User Image Table /////////////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable($installer->getTable("mobikul_userimage"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true],
                "Id"
            )
            ->addColumn("profile", $textType, 255, ["nullable"=>true, "default"=>null], "Profile")
            ->addColumn("banner", $textType, 255, ["nullable"=>true, "default"=>null], "Banner")
            ->addColumn(
                "customer_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Customer Id"
            )
            ->addColumn(
                "is_social",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Is Social"
            )
            ->setComment("Mobikul User Image Table");
        $installer->getConnection()->createTable($table);

        // Mobikul Category Images Table ////////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable($installer->getTable("mobikul_categoryimages"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary"=>true],
                "Id"
            )
            ->addColumn("icon", $textType, null, ["nullable"=>true, "default"=>null], "Icon")
            ->addColumn("banner", $textType, null, ["nullable"=>true, "default"=>null], "Banner")
            ->addColumn(
                "category_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Category Id"
            )
            ->addColumn("category_name", $textType, 255, ["nullable"=>true, "default"=>null], "Category Name")
            ->setComment("Mobikul Category Images Table");
        $installer->getConnection()->createTable($table);

        // Mobikul Category Images Table ////////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable($installer->getTable("mobikul_devicetoken"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary" => true],
                "Id"
            )
            ->addColumn(
                "customer_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Customer Id"
            )
            ->addColumn("token", $textType, 255, ["nullable"=>true, "default"=>null], "Token")
            ->setComment("Mobikul Device Token Table");
        $installer->getConnection()->createTable($table);

        /**
         * Create new table to saving customer token in Mobikul Module
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable("mobikul_oauth_token"))
            ->addColumn(
                "entity_id",
                $integerType,
                null,
                ["identity"=>true, "unsigned"=>true, "nullable"=>false, "primary" => true],
                "Entity Id"
            )
            ->addColumn(
                "customer_id",
                $integerType,
                null,
                ["unsigned"=>true, "nullable"=>false, "default"=>"0"],
                "Customer Id"
            )
            ->addColumn("token", $textType, 255, ["nullable"=>true, "default"=>null], "Token")
            ->addColumn("secret", $textType, 255, ["nullable"=>true, "default"=>null], "Token Secret")
            ->addColumn(
                "created_at",
                $timestampType,
                null,
                ["nullable" => false, "default"=>\Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                "created at"
            )
            ->setComment("Mobikul oauth Token Table");
            $installer->getConnection()->createTable($table);

        /**
         * Add foreign keys for Customer ID
         */
        $installer->getConnection()->addForeignKey(
            $installer->getFkName(
                "mobikul_oauth_token",
                "customer_id",
                "customer_entity",
                "entity_id"
            ),
            $installer->getTable("mobikul_oauth_token"),
            "customer_id",
            $installer->getTable("customer_entity"),
            "entity_id",
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $installer->endSetup();
    }
}
