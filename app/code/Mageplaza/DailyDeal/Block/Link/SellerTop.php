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
use Mageplaza\DailyDeal\Block\Pages\BestsellerDeals;
use Mageplaza\DailyDeal\Model\Config\Source\ShowLinks;

/**
 * Class SellerTop
 * @package Mageplaza\DailyDeal\Block\Link
 */
class SellerTop extends Link
{
    /**
     * @var BestsellerDeals
     */
    protected $_sellerDeals;

    /**
     * SellerTop constructor.
     *
     * @param Context $context
     * @param BestsellerDeals $sellerDeals
     * @param array $data
     */
    public function __construct(
        Context $context,
        BestsellerDeals $sellerDeals,
        array $data = []
    ) {
        $this->_sellerDeals = $sellerDeals;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_sellerDeals->isEnable() || !$this->_sellerDeals->canShowLink(ShowLinks::HEADER_LINK)) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_sellerDeals->getPageUrl();
    }

    /**
     * @return Phrase
     */
    public function getLabel()
    {
        return $this->_sellerDeals->getPageTitle();
    }
}
