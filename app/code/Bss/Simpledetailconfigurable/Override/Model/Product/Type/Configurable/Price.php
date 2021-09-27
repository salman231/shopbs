<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_Simpledetailconfigurable
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Simpledetailconfigurable\Override\Model\Product\Type\Configurable;

use Magento\Catalog\Model\Product;

/**
 * Class Price
 *
 * @package Bss\Simpledetailconfigurable\Override\Model\Product\Type\Configurable
 */
class Price extends \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price
{
    /**
     * @inheritdoc
     */
    public function getFinalPrice($qty, $product)
    {
        if ($qty === null && $product->getCalculatedFinalPrice() !== null) {
            return $product->getCalculatedFinalPrice();
        }
        if ($product->getCustomOption('simple_product') &&
            $product->getCustomOption('simple_product')->getProduct()) {

            /** @var Product $simpleProduct */
            $simpleProduct = $product->getCustomOption('simple_product')->getProduct();

            $quoteItem = $product->getCustomOption('simple_product')->getItem();
            if ($quoteItem) {
                $childs = $quoteItem->getChildren();
                if (count($childs) > 0) {
                    $key = count($childs) - 1;
                    $child = $childs[$key];
                    $simpleProduct = $child->getProduct();
                }
            }

            $simpleProduct->setCustomerGroupId($product->getCustomerGroupId());
            $finalPrice = \Magento\Catalog\Model\Product\Type\Price::getFinalPrice($qty, $simpleProduct);
        } else {
            $priceInfo = $product->getPriceInfo();
            $finalPrice = $priceInfo->getPrice('final_price')->getAmount()->getValue();
        }
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }
}
