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

class CustomUrl extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('sdcp_custom_url', 'url_id');
    }

    /**
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateUrl($data)
    {
        $this->getConnection()->insertMultiple($this->getMainTable(), $data);
    }

    /**
     * @param string $targetUrl
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByUrl($targetUrl)
    {
        $this->getConnection()->delete($this->getMainTable(), ['parent_url=?' => $targetUrl]);
    }

    /**
     * @param string $productId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($productId)
    {
        $this->getConnection()->delete($this->getMainTable(), ['product_id=?' => $productId]);
    }
}
