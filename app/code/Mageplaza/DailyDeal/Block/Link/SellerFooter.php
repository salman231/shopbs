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

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\DailyDeal\Block\Pages\BestsellerDeals;
use Mageplaza\DailyDeal\Block\Pages\NewDeals;
use Mageplaza\DailyDeal\Model\Config\Source\ShowLinks;

/**
 * Class SellerFooter
 * @package Mageplaza\DailyDeal\Block\Link
 */
class SellerFooter extends Current
{
    /**
     * @var NewDeals
     */
    protected $_sellerDeals;

    /**
     * SellerFooter constructor.
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param BestsellerDeals $sellerDeals
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        BestsellerDeals $sellerDeals,
        array $data = []
    ) {
        $this->_sellerDeals = $sellerDeals;

        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_sellerDeals->isEnable() && $this->_sellerDeals->canShowLink(ShowLinks::FOOTER_LINK)) {
            $this->setData([
                'label' => $this->_sellerDeals->getPageTitle(),
                'path'  => $this->_sellerDeals->getRoute(),
            ]);
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
}
