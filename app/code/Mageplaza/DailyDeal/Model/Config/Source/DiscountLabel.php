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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DiscountLabel
 * @package Mageplaza\DailyDeal\Model\Config\Source
 */
class DiscountLabel implements ArrayInterface
{
    const DISABLE             = 1;
    const UNDER_PRICE         = 2;
    const ABOVE_PRODUCT_IMAGE = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::DISABLE, 'label' => __('No')],
            ['value' => self::UNDER_PRICE, 'label' => __('Under Price')],
            ['value' => self::ABOVE_PRODUCT_IMAGE, 'label' => __('Above product image')]
        ];
    }
}
