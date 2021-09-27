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

namespace Mageplaza\DailyDeal\Plugin\Pricing\Render;

use Exception;
use Magento\Catalog\Model\Product;
use Mageplaza\DailyDeal\Helper\Data;

/**
 * Class FinalPriceBox
 * @package Mageplaza\DailyDeal\Plugin\Pricing\Render
 */
class FinalPriceBox
{
    /**
     * Mageplaza\DailyDeal\Helper\Data
     *
     * @var Data
     */
    protected $_helperData;

    /**
     * FinalPriceBox constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox $subject
     * @param $result
     *
     * @return bool
     * @throws Exception
     */
    public function afterHasSpecialPrice(\Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox $subject, $result)
    {
        if (!$this->_helperData->isEnabled()) {
            return $result;
        }

        /** @var Product $product */
        $product      = $subject->getSaleableItem();
        $childProduct = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($childProduct as $child) {
            $childId = $child->getData('entity_id');
            if ($this->_helperData->checkDealProduct($childId)) {
                return true;
            }
        }

        return false;
    }
}
