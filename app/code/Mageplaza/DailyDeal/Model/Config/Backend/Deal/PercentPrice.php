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

namespace Mageplaza\DailyDeal\Model\Config\Backend\Deal;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PercentPrice
 * @package Mageplaza\DailyDeal\Model\Config\Backend\Deal
 */
class PercentPrice extends Value
{
    /**
     * @return Value
     * @throws NoSuchEntityException
     */
    public function beforeSave()
    {
        $percent = $this->getValue();

        if ($percent && ($percent < 0 || $percent > 100)) {
            throw new NoSuchEntityException(
                __('Percent price must be between 0 and 100')
            );
        }

        return parent::beforeSave();
    }
}
