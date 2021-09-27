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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Mageplaza\DailyDeal\Block\Pages\AllDeals;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class AllMenu
 * @package Mageplaza\DailyDeal\Block\Link
 */
class AllMenu extends Template
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var AllDeals
     */
    protected $_allDeals;

    /**
     * AllMenu constructor.
     *
     * @param Template\Context $context
     * @param HelperData $helperData
     * @param AllDeals $allDeals
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        HelperData $helperData,
        AllDeals $allDeals,
        array $data = []
    ) {
        $this->_allDeals   = $allDeals;
        $this->_helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * Get Page Deal Url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPageUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();

        return $baseUrl . $this->getRoute() . $this->_helperData->getUrlSuffix();
    }

    /**
     * Get Page Title All Deals Page
     *
     * @return mixed
     */
    public function getPageTitle()
    {
        return $this->_allDeals->getPageTitle();
    }

    /**
     * Check enable All Deals Page
     *
     * @return mixed
     */
    public function isEnable()
    {
        return $this->_allDeals->isEnable();
    }

    /**
     * Get Route All Deals Page
     *
     * @return mixed
     */
    public function getRoute()
    {
        return $this->_allDeals->getRoute();
    }

    /**
     * Get position show link all deals page
     *
     * @param $position
     *
     * @return bool
     */
    public function canShowLink($position)
    {
        return $this->isEnable() && $this->_allDeals->canShowLink($position);
    }
}
