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

use Mageplaza\DailyDeal\Block\Pages;

/**
 * Class AllDeals
 * @package Mageplaza\DailyDeal\Block\Pages
 */
class AllDeals extends Pages
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
     * Get Page Title All Deals Page
     *
     * @return mixed
     */
    public function getPageTitle()
    {
        $config = $this->getPageConfig('all_deals/title');

        return $config ?: __('All Deals');
    }

    /**
     * Check enable All Deals Page
     *
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getPageConfig('all_deals/enabled');
    }

    /**
     * Get Route All Deals Page
     *
     * @return mixed
     */
    public function getRoute()
    {
        return $this->getPageConfig('all_deals/route');
    }

    /**
     * Get position show link all deals page
     *
     * @return mixed
     */
    public function getShowLinksConfig()
    {
        return $this->getPageConfig('all_deals/show_links');
    }
}
