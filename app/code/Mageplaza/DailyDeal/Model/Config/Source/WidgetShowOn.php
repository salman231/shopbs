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
 * Class WidgetShowOn
 * @package Mageplaza\DailyDeal\Model\Config\Source
 */
class WidgetShowOn implements ArrayInterface
{
    const SIDEBAR_LEFT  = 1;
    const SIDEBAR_RIGHT = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SIDEBAR_LEFT, 'label' => __('Sidebar Top')],
            ['value' => self::SIDEBAR_RIGHT, 'label' => __('Sidebar Bottom')]
        ];
    }
}
