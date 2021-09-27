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

namespace Mageplaza\AutoRelated\Model\Config\Source;

/**
 * Class Type
 * @package Mageplaza\AutoRelated\Model\Config\Source
 */
class Type
{
    const TYPE_PAGE_PRODUCT  = 'product';
    const TYPE_PAGE_CATEGORY = 'category';
    const TYPE_PAGE_SHOPPING = 'cart';
    const TYPE_PAGE_OSC      = 'osc';
    /**
     * Default product page type
     */
    const DEFAULT_TYPE_PAGE = 'product';

    /**
     * @return array
     */
    public function getPageType()
    {
        return [
            self::TYPE_PAGE_PRODUCT  => __('Product Page'),
            self::TYPE_PAGE_CATEGORY => __('Category Page'),
            self::TYPE_PAGE_SHOPPING => __('Shopping Cart Page'),
            self::TYPE_PAGE_OSC      => __('One Page Checkout')
        ];
    }
}
