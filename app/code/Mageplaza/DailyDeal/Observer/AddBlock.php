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

namespace Mageplaza\DailyDeal\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;
use Mageplaza\DailyDeal\Block\Widget\AbstractDeal;
use Mageplaza\DailyDeal\Block\Widget\RandomDeal;
use Mageplaza\DailyDeal\Block\Widget\TopSellingDeal;
use Mageplaza\DailyDeal\Block\Widget\UpcomingDeal;
use Mageplaza\DailyDeal\Helper\Data as HelperData;
use Mageplaza\DailyDeal\Model\Config\Source\WidgetShowOn;

/**
 * Class AddBlock
 * @package Mageplaza\DailyDeal\Observer
 */
class AddBlock implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var AbstractDeal
     */
    protected $_abstractDeal;

    /**
     * AddBlock constructor.
     *
     * @param RequestInterface $request
     * @param HelperData $helperData
     * @param AbstractDeal $abstractDeal
     */
    public function __construct(
        RequestInterface $request,
        HelperData $helperData,
        AbstractDeal $abstractDeal
    ) {
        $this->request       = $request;
        $this->_helperData   = $helperData;
        $this->_abstractDeal = $abstractDeal;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled() || !$this->_abstractDeal->isWidgetEnable()) {
            return false;
        }

        $elementName    = $observer->getElementName();
        $output         = $observer->getTransport()->getOutput();
        $fullActionName = $this->request->getFullActionName();

        $event = $observer->getEvent();
        /** @var Layout $layout */
        $layout = $event->getLayout();

        if (in_array($fullActionName, ['catalog_product_view', 'catalog_category_view', 'checkout_cart_index'])) {
            $types = ['sidebar' => 'catalog.leftnav'];
            $type  = array_search($elementName, $types, true);

            if ($type !== false) {
                $sidebarTopHtml    = '<div id=\"mageplaza-dailydeal-block-top-siderbar\">';
                $sidebarBottomHtml = '<div id=\"mageplaza-dailydeal-block-bottom-siderbar\">';
                $randomHtml        = '';
                $sellingHtml       = '';
                $upcomingHtml      = '';

                if ($this->_abstractDeal->isEnableRandomDeal()) {
                    $randomHtml .= $layout->createBlock(RandomDeal::class)
                        ->setTemplate('Mageplaza_DailyDeal::widget/sidebar-widget.phtml')->toHtml();
                }

                if ($this->_abstractDeal->isEnableSellingDeal()) {
                    $sellingHtml .= $layout->createBlock(TopSellingDeal::class)
                        ->setTemplate('Mageplaza_DailyDeal::widget/sidebar-widget.phtml')->toHtml();
                }

                if ($this->_abstractDeal->isEnableUpcomingDeal()) {
                    $upcomingHtml .= $layout->createBlock(UpcomingDeal::class)
                        ->setTemplate('Mageplaza_DailyDeal::widget/sidebar-widget.phtml')->toHtml();
                }

                if ((int) $this->_abstractDeal->getRandomShowOn() === WidgetShowOn::SIDEBAR_LEFT) {
                    $sidebarTopHtml .= $randomHtml;
                } else {
                    $sidebarBottomHtml .= $randomHtml;
                }

                if ((int) $this->_abstractDeal->getSellingShowOn() === WidgetShowOn::SIDEBAR_LEFT) {
                    $sidebarTopHtml .= $sellingHtml;
                } else {
                    $sidebarBottomHtml .= $sellingHtml;
                }

                if ((int) $this->_abstractDeal->getUpcomingShowOn() === WidgetShowOn::SIDEBAR_LEFT) {
                    $sidebarTopHtml .= $upcomingHtml;
                } else {
                    $sidebarBottomHtml .= $upcomingHtml;
                }

                if ($type === 'sidebar') {
                    $output = $sidebarTopHtml . '</div>' . $output . $sidebarBottomHtml . '</div>';
                }

                $observer->getTransport()->setOutput($output);
            }
        }

        return $this;
    }
}
