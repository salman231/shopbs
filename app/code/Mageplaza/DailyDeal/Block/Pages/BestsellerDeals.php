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
 * Class BestsellerDeals
 * @package Mageplaza\DailyDeal\Block\Pages
 */
class BestsellerDeals extends Pages
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
     * Get Page Title Bestseller Deals Page
     *
     * @return mixed
     */
    public function getPageTitle()
    {
        $config = $this->getPageConfig('best_deals/title');

        return $config ?: __('Bestseller Deals');
    }

    /**
     * Check enable Bestseller Deals Page
     *
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getPageConfig('best_deals/enabled');
    }

    /**
     * Get Route Bestseller Deals Page
     *
     * @return mixed
     */
    public function getRoute()
    {
        return $this->getPageConfig('best_deals/route');
    }

    /**
     * Get position show link Bestseller deals page
     *
     * @return mixed
     */
    public function getShowLinksConfig()
    {
        return $this->getPageConfig('best_deals/show_links');
    }
}
