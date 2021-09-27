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

namespace Mageplaza\DailyDeal\Plugin\Product;

use Closure;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\DailyDeal\Helper\Data as HelperData;

/**
 * Class CountDown
 * @package Mageplaza\DailyDeal\Plugin\Product
 */
class CountDown
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Label constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param ListProduct $subject
     * @param Closure $proceed
     * @param                                            $product
     *
     * @return mixed|string
     * @throws LocalizedException
     */
    public function aroundGetProductDetailsHtml(ListProduct $subject, Closure $proceed, $product)
    {
        $isEnabled = $this->_helperData->isEnabled();
        if (!$isEnabled) {
            return $proceed($product);
        }

        $result = $proceed($product);
        if ($subject->getRequest()->isAjax()) {
            $result .= $subject->getLayout()
                ->createBlock('Mageplaza\DailyDeal\Block\Product\View\Countdown')
                ->setTemplate('Mageplaza_DailyDeal::category/view/countdown.phtml')
                ->setCatProductId($product->getId())
                ->toHtml();
        }

        return $result;
    }
}
