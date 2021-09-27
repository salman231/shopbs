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

namespace Mageplaza\DailyDeal\Block\Pages;

use Magento\Framework\Phrase;
use Mageplaza\DailyDeal\Block\Pages;

/**
 * Class FeaturedDeals
 * @package Mageplaza\DailyDeal\Block\Pages
 */
class FeaturedDeals extends Pages
{
    /**
     * @return $this|Pages
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $title = $this->getPageTitle();
        $this->pageConfig->getTitle()->set($title);

        return $this;
    }

    /**
     * Get Page Title Featured Deals Page
     *
     * @return Phrase|mixed
     */
    public function getPageTitle()
    {
        $config = $this->getPageConfig('featured_deals/title');

        return $config ?: __('Featured Deals');
    }

    /**
     * Check enable Featured Deals Page
     *
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getPageConfig('featured_deals/enabled');
    }

    /**
     * Get Route Featured Deals Page
     *
     * @return mixed
     */
    public function getRoute()
    {
        return $this->getPageConfig('featured_deals/route');
    }

    /**
     * Get position show link Featured deals page
     *
     * @return mixed
     */
    public function getShowLinksConfig()
    {
        return $this->getPageConfig('featured_deals/show_links');
    }
}
