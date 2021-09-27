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
 * @category  Mageplaza
 * @package   Mageplaza_DailyDeal
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Frequency
 * @package Mageplaza\DailyDeal\Model\Config\Source
 */
class Frequency implements ArrayInterface
{
    const DISABLE      = 0;
    const CRON_DAILY   = 'D';
    const CRON_WEEKLY  = 'W';
    const CRON_MONTHLY = 'M';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Disable'), 'value' => self::DISABLE],
            ['label' => __('Daily'), 'value' => self::CRON_DAILY],
            ['label' => __('Weekly'), 'value' => self::CRON_WEEKLY],
            ['label' => __('Monthly'), 'value' => self::CRON_MONTHLY],
        ];
    }
}
