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
namespace Bss\Simpledetailconfigurable\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PreselectKey extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdcp_preselect', 'preselect_id');
    }

    /**
     * @param string $productId
     * @param string $key
     * @param string $value
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function savePreselectKey($productId, $key, $value)
    {
        $connection = $this->getConnection();
        $bind = [
            'product_id' => $productId,
            'attribute_key' => $key,
            'value_key' => $value
        ];
        if ($value != '') {
            $connection->insert($this->getMainTable(), $bind);
        }
    }

    /**
     * @param string $productId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteOldKey($productId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['product_id=?' => $productId]);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function truncate()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
