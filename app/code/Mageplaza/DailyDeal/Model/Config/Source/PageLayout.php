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
 * Class PageLayout
 * @package Mageplaza\DailyDeal\Model\Config\Source
 */
class PageLayout implements ArrayInterface
{
    const ONE_COLUMN       = 1;
    const TWO_COLUMN_LEFT  = 2;
    const TWO_COLUMN_RIGHT = 3;
    const THREE_COLUMN     = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ONE_COLUMN, 'label' => __('1 column')],
            ['value' => self::TWO_COLUMN_LEFT, 'label' => __('2 columns with left sidebar')],
            ['value' => self::TWO_COLUMN_RIGHT, 'label' => __('2 columns with right sidebar')],
            ['value' => self::THREE_COLUMN, 'label' => __('3 columns')]
        ];
    }
}
