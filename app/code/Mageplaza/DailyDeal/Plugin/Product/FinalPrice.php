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

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\DailyDeal\Helper\Data;

/**
 * Class FinalPrice
 * @package Mageplaza\DailyDeal\Plugin\Product
 */
class FinalPrice
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * PriceDeal constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param \Magento\Catalog\Pricing\Price\FinalPrice $subject
     * @param $result
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetValue(\Magento\Catalog\Pricing\Price\FinalPrice $subject, $result)
    {
        if (!$this->_helperData->isEnabled()) {
            return $result;
        }
        $productId = $subject->getProduct()->getId();
        if ($productId && $this->_helperData->checkDealProduct($productId)) {
            return $this->_helperData->getDealPrice($productId) ?: $result;
        }

        return $result;
    }
}
