<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Helper;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\FrequentlyBought\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'autorelated';
    const CONFIG_POPUP_PATH  = 'autorelated/popup';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Registry $registry,
        Session $customerSession
    ) {
        $this->registry        = $registry;
        $this->customerSession = $customerSession;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $click
     * @param $impression
     *
     * @return string
     */
    public function getCtr($click, $impression)
    {
        $ctr = $click / $impression * 100;

        return sprintf('%.2f', $ctr) . '%';
    }

    /**
     * @return Product
     */
    public function getCurrentProduct()
    {
        if ($this->_request->isAjax()) {
            $productId = $this->getData('entity_id');
            $product   = $this->objectManager->create(Product::class)->load($productId);
        } else {
            $product = $this->registry->registry('current_product');
        }

        return $product;
    }

    /**
     * @return Category
     */
    public function getCurrentCategory()
    {
        if ($this->_request->isAjax()) {
            $categoryId = $this->getData('entity_id');
            $category   = $this->objectManager->create(Category::class)->load($categoryId);
        } else {
            $category = $this->registry->registry('current_category');
        }

        return $category;
    }

    /**
     * Get Configuration Popup
     *
     * @param string $code
     * @param null $store
     *
     * @return array|mixed
     */
    public function getConfigPopup($code = '', $store = null)
    {
        $code = $code ? self::CONFIG_POPUP_PATH . '/' . $code : self::CONFIG_POPUP_PATH;

        return $this->getConfigValue($code, $store);
    }

    /**
     * @return mixed|null
     */
    public function getCustomerGroup()
    {
        return $this->customerSession->getCustomerGroupId();
    }

    /**
     * @return int
     */
    public function getCurrentStore()
    {
        return $this->storeManager->getStore()->getId();
    }
}
