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

class ProductEnabledModule extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdcp_product_enabled', 'product_id');
    }

    /**
     * @param string $productId
     * @param string  $enabled
     * @param string $isAjax
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveEnabled($productId, $enabled, $isAjax)
    {
        $connection = $this->getConnection();
        $bind = [
            'product_id' => $productId,
            'enabled' => $enabled,
            'is_ajax_load' => $isAjax
        ];
        $connection->insert($this->getMainTable(), $bind);
    }

    /**
     * @param string $productId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteOldKey($productId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['product_id=?' => $productId]);
    }
}
