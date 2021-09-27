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

namespace Bss\Simpledetailconfigurable\Model\ResourceModel\SourceStock;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class SalesChannel
 *
 * @package Bss\Simpledetailconfigurable\Model\ResourceModel\SourceStock
 */
class SalesChannel extends AbstractDb
{
    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('inventory_stock_sales_channel', 'stock_id');
    }

    /**
     * Get stock id by website code
     *
     * @param string $websiteCode
     * @return bool|mixed
     * @throws \Zend_Db_Statement_Exception
     */
    public function getStockIdByWebsiteCode($websiteCode)
    {
        $data = [];
        $connection = $this->getConnection();
        $channelTable = $this->getTable('inventory_stock_sales_channel');
        $select = $connection->select()
            ->from(
                $channelTable,
                ['stock_id']
            )->where('code = ? and type = "website"', $websiteCode);

        $query = $connection->query($select);

        while ($row = $query->fetch()) {
            array_push($data, $row);
        }
        if (!empty($data)) {
            return $data[0]['stock_id'];
        }

        return false;
    }

    /**
     * Get stock detail by product sku
     *
     * @param int $stockId
     * @param string $productSku
     * @return array|bool
     */
    public function getStockDetail($stockId, $productSku)
    {
        $data = [];
        $connection = $this->getConnection();
        $stockTable = $this->getStockTableName($stockId);
        if ($stockTable) {
            $select = $connection->select()
                ->from(
                    $stockTable
                )->where('sku = ?', $productSku);
            $data = $connection->fetchRow($select);
        }

        if (is_array($data) && !empty($data)) {
            return $data;
        }
        return false;
    }

    /**
     * Get stock table name
     *
     * @param int $stockId
     * @return bool|string
     */
    public function getStockTableName($stockId)
    {
        try {
            return $this->getTable('inventory_stock_' . $stockId);
        } catch (\Exception $exception) {
            return false;
        }
    }
}
