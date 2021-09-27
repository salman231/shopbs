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

namespace Mageplaza\DailyDeal\Block\Product\View;

use Exception;
use Mageplaza\DailyDeal\Block\Deal;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class Countdown
 * @package Mageplaza\DailyDeal\Block\Product\View
 */
class Countdown extends Deal
{
    /**
     * Get show countdown config
     *
     * @return mixed
     */
    public function isShowCountdown()
    {
        return $this->_helperData->getConfigGeneral('countdown_timer/show_countdown_timer');
    }

    /**
     * @param $productId
     *
     * @return float|int
     * @throws Exception
     */
    public function getTimeCountdown($productId)
    {
        $dealData = $this->_helperData->getProductDeal($productId);
        if (!$dealData->getId()) {
            return 0;
        }

        return $this->_helperData->getRemainTime($dealData);
    }

    /**
     * @return mixed
     */
    public function getClockStyle()
    {
        return $this->_helperData->getConfigGeneral('countdown_timer/clock_style');
    }

    /**
     * Get Outer background color config
     *
     * @return mixed
     */
    public function getOuterCountdownBg()
    {
        return $this->_helperData->getConfigGeneral('countdown_timer/countdown_outer_color');
    }

    /**
     * Get Inner background color config
     *
     * @return mixed
     */
    public function getInnerCountdownBg()
    {
        return $this->_helperData->getConfigGeneral('countdown_timer/countdown_inner_color');
    }

    /**
     * Get Text Color Countdown timer config
     *
     * @return mixed
     */
    public function getTextColorCountdown()
    {
        return $this->_helperData->getConfigGeneral('countdown_timer/countdown_text');
    }

    /**
     * Get Number Color Countdown timer config
     *
     * @return mixed
     */
    public function getNumberColorCountdown()
    {
        return $this->_helperData->getConfigGeneral('countdown_timer/countdown_number');
    }

    /**
     * @param $productId
     *
     * @return bool
     */
    public function isSimpleProduct($productId)
    {
        $product = $this->_helperData->_productFactory->create()->load($productId);

        return $product->getTypeId() === 'simple' || $product->getTypeId() === 'virtual';
    }

    /**
     * Send Countdown data to js
     *
     * @param $productId
     *
     * @return string
     * @throws Exception
     */
    public function getTimeCountdownData($productId)
    {
        $params = [
            'isSimpleProduct' => $this->isSimpleProduct($productId),
            'countdown'       => $this->getTimeCountdown($productId),
            'prodId'          => $productId,
            'countdownUrl'    => $this->getUrl('dailydeal/deal/countdown')
        ];

        return HelperData::jsonEncode($params);
    }
}
