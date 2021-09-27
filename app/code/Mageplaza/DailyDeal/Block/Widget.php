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

namespace Mageplaza\DailyDeal\Block;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\DailyDeal\Block\Widget\AbstractDeal;

/**
 * Class Widget
 * @package Mageplaza\DailyDeal\Block
 */
class Widget extends AbstractDeal
{
    const ALL_DEAL        = 'all';
    const FEATURE_DEAL    = 'feature';
    const NEW_DEAL        = 'new';
    const UPCOMING_DEAL   = 'upcoming';
    const BESTSELLER_DEAL = 'bestseller';
    const RANDOM_DEAL     = 'random';

    /**
     * Template
     */
    protected $_template = 'Mageplaza_DailyDeal::widget/widget.phtml';

    /**
     * Get Type Deal
     *
     * @return mixed|string
     */
    public function getTypeWidget()
    {
        return $this->hasData('type') ? $this->getData('type') : 'random';
    }

    /**
     * Get type display
     *
     * @return mixed|string
     */
    public function getTypeDisplay()
    {
        return $this->hasData('display') ? $this->getData('display') : 'slider';
    }

    /**
     * Get Title
     *
     * @return mixed|string
     */
    public function getTitle()
    {
        return $this->hasData('title') ? $this->getData('title') : __('DAILY DEAL PRODUCTS');
    }

    /**
     * Get Limit
     *
     * @return mixed|string
     */
    public function getLimit()
    {
        return $this->hasData('limit') ? $this->getData('limit') : 5;
    }

    /**
     * Get Product Collection by type deal
     *
     * @return array
     * @throws LocalizedException
     */
    public function getProductCollection()
    {
        $collection = [];

        if ($type = $this->getTypeWidget()) {
            $collection = $this->getLayout()->createBlock($this->getBlockMap($type))
                ->getProductCollection($this->getLimit());
        }

        return $collection;
    }

    /**
     * @param null $type
     *
     * @return array|mixed
     */
    public function getBlockMap($type = null)
    {
        $maps = [
            self::ALL_DEAL        => 'Mageplaza\DailyDeal\Block\Widget\AllDeal',
            self::FEATURE_DEAL    => 'Mageplaza\DailyDeal\Block\Widget\FeatureDeal',
            self::NEW_DEAL        => 'Mageplaza\DailyDeal\Block\Widget\NewDeal',
            self::UPCOMING_DEAL   => 'Mageplaza\DailyDeal\Block\Widget\UpcomingDeal',
            self::BESTSELLER_DEAL => 'Mageplaza\DailyDeal\Block\Widget\TopSellingDeal',
            self::RANDOM_DEAL     => 'Mageplaza\DailyDeal\Block\Widget\RandomDeal',
        ];

        if ($type && isset($maps[$type])) {
            return $maps[$type];
        }

        return $maps;
    }

    /**
     * widget Label
     *
     * @param $id
     *
     * @return string
     */
    public function widgetLabel($id)
    {
        return $this->_label->getLabel($this->_label->getPercentDiscount($id));
    }

    /**
     * @return Product\View\Label
     */
    public function label()
    {
        return $this->_label;
    }
}
