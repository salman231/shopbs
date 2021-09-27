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
 * @package     ${MODULENAME}
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ClockStyle
 * @package Mageplaza\DailyDeal\Model\Config\Source
 */
class ClockStyle implements ArrayInterface
{
    const STYLE_1 = 'deal_style_1';
    const STYLE_2 = 'deal_style_2';
    const STYLE_3 = 'deal_style_3';
    const STYLE_4 = 'deal_style_4';
    const STYLE_5 = 'deal_style_5';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STYLE_1, 'label' => __('Simple')],
            ['value' => self::STYLE_2, 'label' => __('Circle')],
            ['value' => self::STYLE_3, 'label' => __('Square')],
            ['value' => self::STYLE_4, 'label' => __('Stack')],
            ['value' => self::STYLE_5, 'label' => __('Modern')]
        ];
    }
}
