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
use Mageplaza\DailyDeal\Model\Config\Source\RoundPercent;
use Mageplaza\DailyDeal\Model\Config\Source\ShowOnProductImage;

/**
 * Class Label
 * @package Mageplaza\DailyDeal\Block\Product\View
 */
class Label extends Deal
{
    /**
     * Get Percent Discount
     *
     * @param null $productId
     *
     * @return float|int|string
     */
    public function getPercentDiscount($productId = null)
    {
        $productId = $productId ?: $this->getCurrentProduct()->getId();

        $dealCollection = $this->_helperData->getProductDeal($productId);
        $oldPrice       = (float) $this->_helperData->getProductPrice($productId);
        $dealPrice      = (float) $dealCollection->getDealPrice();

        if (!$oldPrice) {
            return 0;
        }

        $roundPercent = $this->getRoundPercentConfig();

        switch ($roundPercent) {
            case RoundPercent::ROUND_UP:
                $percent = ceil(($oldPrice - $dealPrice) * 100 / $oldPrice);
                break;
            case RoundPercent::ROUND_DOWN:
                $percent = floor(($oldPrice - $dealPrice) * 100 / $oldPrice);
                break;
            default: //disable
                $percent = number_format(($oldPrice - $dealPrice) * 100 / $oldPrice, 2);
                break;
        }

        return $percent;
    }

    /**
     * Get Round Percent
     *
     * @return mixed
     */
    public function getRoundPercentConfig()
    {
        return $this->_helperData->getConfigGeneral('discount_label/round_percent');
    }

    /**
     * Get max percent of configuration product
     *
     * @param $productId
     *
     * @return mixed
     * @throws Exception
     */
    public function getMaxPercent($productId)
    {
        $percents = [];
        $childIds = $this->_helperData->getChildConfigurableProductIds($productId);

        foreach ($childIds as $childId) {
            if ($this->_helperData->checkStatusDeal($childId)) {
                $percents[] = $this->getPercentDiscount($childId);
            }
        }
        rsort($percents, SORT_NUMERIC);

        return $percents[0];
    }

    /**
     * Get position to show label
     *
     * @return mixed
     */
    public function getShowLabelConfig()
    {
        return (int) $this->_helperData->getConfigGeneral('discount_label/show_discount_label');
    }

    /**
     * Get background color label
     *
     * @return mixed
     */
    public function getBackgroundColor()
    {
        return $this->_helperData->getConfigGeneral('discount_label/label_bg_color');
    }

    /**
     * Get border color label
     *
     * @return mixed
     */
    public function getBorderStyle()
    {
        $borderColor = $this->_helperData->getConfigGeneral('discount_label/label_border_color');

        return 'solid 1px ' . $borderColor;
    }

    /**
     * Get text color label
     *
     * @return mixed
     */
    public function getTextColor()
    {
        return $this->_helperData->getConfigGeneral('discount_label/label_text_color');
    }

    /**
     * Get location label on image
     *
     * @return mixed
     */
    public function getLocationLabelOnImage()
    {
        return $this->_helperData->getConfigGeneral('discount_label/show_on');
    }

    /**
     * Get label text
     *
     * @param $percent
     *
     * @return mixed
     */
    public function getLabel($percent)
    {
        $contentLabel = $this->_helperData->getConfigGeneral('discount_label/content_label');
        $label        = $contentLabel ?: '{{number}}';

        return str_replace('{{number}}', $percent, $label);
    }

    /**
     * CSS of label on image
     *
     * @return string
     */
    public function getCssLabelOnImage()
    {
        $css      = '';
        $location = $this->getLocationLabelOnImage();

        switch ($location) {
            case ShowOnProductImage::TOP_LEFT:
                $css = 'top: 10px';
                break;
            case ShowOnProductImage::TOP_RIGHT:
                $css = 'top: 10px; right: 0';
                break;
            case ShowOnProductImage::BOTTOM_LEFT:
                $css = 'bottom: 10px;';
                break;
            case ShowOnProductImage::BOTTOM_RIGHT:
                $css = 'bottom: 10px; right: 0';
                break;
        }

        return $css;
    }

    /**
     * Check position label = bottom image
     *
     * @return bool
     */
    public function isLabelBottom()
    {
        return (int) $this->getLocationLabelOnImage() === ShowOnProductImage::BOTTOM_LEFT
            || (int) $this->getLocationLabelOnImage() === ShowOnProductImage::BOTTOM_RIGHT;
    }
}
