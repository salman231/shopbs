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
namespace Bss\Simpledetailconfigurable\Model\ResourceModel\PreselectKey;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Bss\Simpledetailconfigurable\Model\PreselectKey::class,
            \Bss\Simpledetailconfigurable\Model\ResourceModel\PreselectKey::class
        );
    }

    /**
     * @return $this
     */
    public function joinCatalog()
    {
        $this->join(
            "catalog_product_entity",
            "catalog_product_entity.entity_id = product_id",
            "catalog_product_entity.sku"
        );
        return $this;
    }

    /**
     * @param string $productId
     * @return array
     */
    public function getArrayData($productId)
    {
        $this->addFieldToFilter('product_id', $productId);
        $result = [];
        foreach ($this->getItems() as $item) {
            $result[$item->getAttributeKey()] = $item->getValueKey();
        }
        return $result;
    }

    /**
     *
     */
    public function delete()
    {
        $this->getConnection()->deleteFromSelect($this->getSelect(), $this->getMainTable());
    }
}
