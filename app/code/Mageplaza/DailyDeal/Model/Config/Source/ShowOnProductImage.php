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
 * Class ShowOnProductImage
 * @package Mageplaza\DailyDeal\Model\Config\Source
 */
class ShowOnProductImage implements ArrayInterface
{
    const TOP_LEFT     = 1;
    const TOP_RIGHT    = 2;
    const BOTTOM_LEFT  = 3;
    const BOTTOM_RIGHT = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TOP_LEFT, 'label' => __('Top Left')],
            ['value' => self::TOP_RIGHT, 'label' => __('Top Right')],
            ['value' => self::BOTTOM_LEFT, 'label' => __('Bottom Left')],
            ['value' => self::BOTTOM_RIGHT, 'label' => __('Bottom Right')]
        ];
    }
}
