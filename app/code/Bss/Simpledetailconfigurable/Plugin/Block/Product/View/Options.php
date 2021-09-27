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

namespace Bss\Simpledetailconfigurable\Plugin\Block\Product\View;

use Magento\Catalog\Model\Product\Option\Value;

class Options
{
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * Options constructor.
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
     */
    public function __construct(
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->_jsonDecoder = $jsonDecoder;
        $this->_jsonEncoder = $jsonEncoder;
        $this->productRepository = $productRepository;
        $this->pricingHelper = $pricingHelper;
        $this->_catalogData = $catalogData;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param \Magento\Catalog\Block\Product\View\Options $subject
     * @param string $result
     * @return string
     * @throws NoSuchEntityException
     */
    public function afterGetJsonConfig($subject, $result)
    {
        $config = $this->_jsonDecoder->decode($result);
        if ($this->moduleConfig->isModuleEnable()
            && $this->moduleConfig->isEnableChildOption()
        ) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $subject->getProduct();
            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {

                $childrens = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($childrens as $child) {
                    $child = $this->getProduct($child->getId());
                    $this->getChildOptions($child, $config);
                }
            }
        }
        return $this->_jsonEncoder->encode($config);
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    private function getProduct($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $child
     * @param $config
     */
    protected function getChildOptions($child, &$config)
    {
        if ($child->getOptions()) {
            foreach ($child->getOptions() as $option) {
                if ($option->hasValues()) {
                    $tmpPriceValues = [];
                    foreach ($option->getValues() as $valueId => $value) {
                        $tmpPriceValues[$valueId] = $this->_getPriceConfiguration($value);
                    }
                    $priceValue = $tmpPriceValues;
                } else {
                    $priceValue = $this->_getPriceConfiguration($option);
                }
                $config[$option->getId()] = $priceValue;
            }
        }
    }

    /**
     * Get price configuration
     *
     * @param \Magento\Catalog\Model\Product\Option\Value|\Magento\Catalog\Model\Product\Option $option
     * @return array
     */
    protected function _getPriceConfiguration($option)
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
                        $option->getProduct(),
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
                        $option->getProduct(),
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
