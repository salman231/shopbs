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
use Exception;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Mageplaza\DailyDeal\Helper\Data;

/**
 * Class PriceDeal
 * @package Mageplaza\DailyDeal\Plugin\Product
 */
class PriceDeal
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
     * @param AbstractProduct $subject
     * @param Closure $proceed
     * @param Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     *
     * @return mixed
     * @throws Exception
     */
    public function aroundGetReviewsSummaryHtml(
        AbstractProduct $subject,
        Closure $proceed,
        $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        if (!$this->_helperData->isEnabled()) {
            return $proceed($product, $templateType, $displayIfNoReviews);
        }

        if ($product->getTypeInstance() instanceof Configurable) {
            $childProduct = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($childProduct as $child) {
                $childId = $child->getData('entity_id');
                if ($this->_helperData->checkDealProduct($childId)) {
                    $dealPrice = $this->_helperData->getDefaultDealPrice($childId);
                    $child->setSpecialPrice($dealPrice);
                }
            }
        }

        return $proceed($product, $templateType, $displayIfNoReviews);
    }
}
