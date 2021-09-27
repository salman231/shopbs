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

namespace Bss\Simpledetailconfigurable\Model\ResourceModel\Catalog;

class Product extends \Magento\Sitemap\Model\ResourceModel\Catalog\Product
{
    /**
     * @param array $productRow
     * @param string $storeId
     * @param \Magento\Framework\DataObject$newProduct
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareProductCustom(array $productRow, $storeId, $newProduct)
    {
        $newProduct['id'] = $productRow[$this->getIdFieldName()];
        if (empty($productRow['url'])) {
            $productRow['url'] = 'catalog/product/view/id/' . $newProduct->getId();
        }
        $newProduct->addData($productRow);
        $this->_loadProductImages($newProduct, $storeId);

        return $newProduct;
    }
}
