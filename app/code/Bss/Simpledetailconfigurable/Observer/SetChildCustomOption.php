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

namespace Bss\Simpledetailconfigurable\Observer;

use Magento\Catalog\Model\Product\Option\Value;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SetChildCustomOption
 *
 * @package Bss\Simpledetailconfigurable\Observer
 */
class SetChildCustomOption implements ObserverInterface
{
    /**
     * Product option
     *
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $_option;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * SetChildCustomOption constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Product\Option $option
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        $this->registry = $registry;
        $this->_option = $option;
        $this->_catalogData = $catalogData;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $configObj = $observer->getData('configObj');
        $config = $observer->getData('configObj')['config'];
        $configurableProduct = $this->registry->registry('product');
        if ($configurableProduct->getTypeId() == Configurable::TYPE_CODE) {
            $products = $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);
            foreach ($products as $product) {
                $options = $this->_option->getProductOptions($product);
                if ($options) {
                    foreach ($options as $option) {
                        $tmpPriceValues = $this->getTmpPriceValues($option, $product);
                        $priceValue = $tmpPriceValues;
                        $config[$option->getId()] = $priceValue;
                    }
                }
            }
            $configObj->setConfig($config);
        }
    }

    /**
     * @param $option
     * @param $product
     * @return array
     */
    protected function getTmpPriceValues($option, $product)
    {
        $tmpPriceValues = [];
        if ($option->getValues()) {
            foreach ($option->getValues() as $valueId => $value) {
                $tmpPriceValues[$valueId] = $this->_getPriceConfiguration($value, $product);
            }
        }
        return $tmpPriceValues;
    }

    /**
     * Get price configuration
     *
     * @param \Magento\Catalog\Model\Product\Option\Value|\Magento\Catalog\Model\Product\Option $option
     * @return array
     */
    protected function _getPriceConfiguration($option, $product)
    {
        $optionPrice = $option->getPrice(true);
        if ($option->getPriceType() !== Value::TYPE_PERCENT) {
            $optionPrice = $this->pricingHelper->currency($optionPrice, false, false);
        }
        $data = [
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->pricingHelper->currency($option->getRegularPrice(), false, false),
                    'adjustments' => [],
                ],
                'basePrice' => [
                    'amount' => $this->_catalogData->getTaxPrice(
                        $product,
                        $optionPrice,
                        false,
                        null,
                        null,
                        null,
                        null,
                        null,
                        false
                    ),
                ],
                'finalPrice' => [
                    'amount' => $this->_catalogData->getTaxPrice(
                        $product,
                        $optionPrice,
                        true,
                        null,
                        null,
                        null,
                        null,
                        null,
                        false
                    ),
                ],
            ],
            'type' => $option->getPriceType(),
            'name' => $option->getTitle(),
        ];
        return $data;
    }
}
