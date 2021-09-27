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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param  SchemaSetupInterface   $setup
     * @param  ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $textType = \Magento\Framework\DB\Ddl\Table::TYPE_TEXT;
        $decimalType = \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL;
        $integerType = \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER;
        $timestampType = \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP;
        $timestampInit = \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT;
        $timestampInitUpdate = \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE;

        // Delivery Boy Table /////////////////////////////////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable(
                $installer->getTable("deliveryboy_deliveryboy")
            )->addColumn(
                "id",
                $integerType,
                null,
                ["identity" => true, "unsigned" => true, "nullable" => false, "primary" => true],
                "Id"
            )->addColumn(
                "image",
                $textType,
                null,
                ["nullable" => true, "default" => null],
                "Profile image"
            )->addColumn(
                "name",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Deliveryboy name"
            )->addColumn(
                "status",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Status"
            )->addColumn(
                "email",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Email"
            )->addColumn(
                "mobile_number",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Mobile Number"
            )->addColumn(
                "vehicle_number",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Vehicle Number"
            )->addColumn(
                "availability_status",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Availability Status"
            )->addColumn(
                "vehicle_type",
                $textType,
                255,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Vehicle Type"
            )->addColumn(
                "password",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Password"
            )->addColumn(
                "address",
                $textType,
                null,
                ["nullable" => true, "default" => null],
                "Full Address of deliveryboy"
            )->addColumn(
                "latitude",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Deliveryboy current Latitude"
            )->addColumn(
                "longitude",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Deliveryboy current Longitude"
            )->addColumn(
                "created_at",
                $timestampType,
                null,
                ["nullable" => false, "default" => $timestampInit],
                "Creation Time"
            )->addColumn(
                "updated_at",
                $timestampType,
                null,
                ["nullable" => false, "default" => $timestampInitUpdate],
                "Updation Time"
            )->addColumn(
                "rp_token",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Reset password token"
            )->addColumn(
                "rp_token_created_at",
                $timestampType,
                null,
                ["nullable" => false, "default" => $timestampInitUpdate],
                "Reset password request time"
            )->setComment(
                "Delivery Boy Table"
            );
        $installer->getConnection()->createTable($table);

        // Delivery Boy Rating Table //////////////////////////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable($installer->getTable("deliveryboy_rating"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity" => true, "unsigned" => true, "nullable" => false, "primary" => true],
                "Id"
            )
            ->addColumn(
                "deliveryboy_id",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Deliveryboy Id"
            )
            ->addColumn(
                "title",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Review Title"
            )
            ->addColumn(
                "comment",
                $textType,
                null,
                ["nullable" => true, "default" => null],
                "Review comment"
            )
            ->addColumn(
                "customer_id",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Customer Id"
            )
            ->addColumn(
                "rating",
                $decimalType,
                "12,4",
                [],
                "Deliveryboy rating"
            )
            ->addColumn(
                "status",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Status"
            )
            ->addColumn(
                "created_at",
                $timestampType,
                null,
                ["nullable" => false, "default" => $timestampInit],
                "Creation Time"
            )
            ->setComment(
                "Delivery Boy Rating Table"
            );
        $installer->getConnection()->createTable($table);

        // Delivery Boy Orders Table //////////////////////////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable($installer->getTable("deliveryboy_orders"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity" => true, "unsigned" => true, "nullable" => false, "primary" => true],
                "Id"
            )
            ->addColumn(
                "deliveryboy_id",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Deliveryboy Id"
            )
            ->addColumn(
                "order_id",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Real order id"
            )
            ->addColumn(
                "increment_id",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Order increment id"
            )
            ->addColumn(
                "order_status",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Order status"
            )
            ->addColumn(
                "otp",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "OTP for order completion"
            )
            ->setComment(
                "Delivery Boy Orders Table"
            );
        $installer->getConnection()->createTable($table);

        // Delivery Boy Orders Table //////////////////////////////////////////////////////////////
        $table = $installer->getConnection()
            ->newTable($installer->getTable("deliveryboy_devicetoken"))
            ->addColumn(
                "id",
                $integerType,
                null,
                ["identity" => true, "unsigned" => true, "nullable" => false, "primary" => true],
                "Id"
            )
            ->addColumn(
                "deliveryboy_id",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Deliveryboy Id"
            )
            ->addColumn(
                "token",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Token"
            )
            ->addColumn(
                "os",
                $textType,
                255,
                ["nullable" => true, "default" => null],
                "Operating system"
            )
            ->addColumn(
                "is_admin",
                $integerType,
                null,
                ["unsigned" => true, "nullable" => false, "default" => "0"],
                "Is Admin token"
            )
            ->setComment(
                "Delivery Boy Device Token Table"
            );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
