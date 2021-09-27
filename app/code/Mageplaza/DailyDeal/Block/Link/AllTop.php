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
use Mageplaza\DailyDeal\Block\Pages\AllDeals;
use Mageplaza\DailyDeal\Model\Config\Source\ShowLinks;

/**
 * Class AllTop
 * @package Mageplaza\DailyDeal\Block\Link
 */
class AllTop extends Link
{
    /**
     * @var AllDeals
     */
    protected $_allDeals;

    /**
     * AllTop constructor.
     *
     * @param Context $context
     * @param AllDeals $allDeals
     * @param array $data
     */
    public function __construct(
        Context $context,
        AllDeals $allDeals,
        array $data = []
    ) {
        $this->_allDeals = $allDeals;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_allDeals->isEnable() || !$this->_allDeals->canShowLink(ShowLinks::HEADER_LINK)) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_allDeals->getPageUrl();
    }

    /**
     * @return Phrase
     */
    public function getLabel()
    {
        return $this->_allDeals->getPageTitle();
    }
}
