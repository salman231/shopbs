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

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Direction
 * @package Mageplaza\AutoRelated\Model\Config\Source
 */
class Direction implements ArrayInterface
{
    const BESTSELLER = 1;
    const PRICE_LOW  = 2;
    const PRICE_HIGH = 3;
    const NEWEST     = 4;
    const RANDOM     = 5;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BESTSELLER, 'label' => __('Bestsellers')],
            ['value' => self::PRICE_LOW, 'label' => __('Lowest Price')],
            ['value' => self::PRICE_HIGH, 'label' => __('Highest Price')],
            ['value' => self::NEWEST, 'label' => __('Newest')],
            ['value' => self::RANDOM, 'label' => __('Random')]
        ];
    }
}
