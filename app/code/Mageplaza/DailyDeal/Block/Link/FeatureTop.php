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

namespace Mageplaza\DailyDeal\Block\Link;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\DailyDeal\Block\Pages\FeaturedDeals;
use Mageplaza\DailyDeal\Model\Config\Source\ShowLinks;

/**
 * Class FeatureTop
 * @package Mageplaza\DailyDeal\Block\Link
 */
class FeatureTop extends Link
{
    /**
     * @var FeaturedDeals
     */
    protected $_featureDeals;

    /**
     * FeatureTop constructor.
     *
     * @param Context $context
     * @param FeaturedDeals $featuredDeals
     * @param array $data
     */
    public function __construct(
        Context $context,
        FeaturedDeals $featuredDeals,
        array $data = []
    ) {
        $this->_featureDeals = $featuredDeals;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_featureDeals->isEnable() || !$this->_featureDeals->canShowLink(ShowLinks::HEADER_LINK)) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_featureDeals->getPageUrl();
    }

    /**
     * @return Phrase
     */
    public function getLabel()
    {
        return $this->_featureDeals->getPageTitle();
    }
}
