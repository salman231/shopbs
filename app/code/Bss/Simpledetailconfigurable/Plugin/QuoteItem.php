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
namespace Bss\Simpledetailconfigurable\Plugin;

class QuoteItem
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    private $helper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * QuoteItem constructor.
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $helper
    ) {
        $this->serializer = $serializer;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param callable $proceed
     * @param $options1
     * @param $options2
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundCompareOptions(
        \Magento\Quote\Model\Quote\Item $subject,
        callable $proceed,
        $options1,
        $options2
    ) {
        if ($this->helper->isModuleEnable() &&
            $this->helper->isEnableChildOption()) {
            $_notRepresentOptions = ['info_buyRequest','additional_options'];
            foreach ($options1 as $option) {
                $code = $option->getCode();
                // compare custom option
                if ($code == 'info_buyRequest') {
                    $data1 = isset($options2[$code]) ? $this->serializer->unserialize($options2[$code]->getValue()) : [];
                    $data2 = $option ? $this->serializer->unserialize($option->getValue()) : [];
                    $_options1 =  isset($data1['options']) ? $data1['options'] : [];
                    $_options2 =  isset($data2['options']) ? $data2['options'] : [];
                    if ($_options1 != $_options2) {
                        return false;
                    }
                }

                if (in_array($code, $_notRepresentOptions)) {
                    continue;
                }
                if (!isset($options2[$code]) || $options2[$code]->getValue() != $option->getValue()) {
                    return false;
                }
            }
            return true;
        }
        return $proceed($options1, $options2);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param array $result
     * @return array
     */
    public function afterToArray(
        \Magento\Quote\Model\Quote\Item $subject,
        $result
    ) {
        if ($this->helper->isShowName() && $child = $this->getChildProduct($subject)) {
            $result['name'] = $child->getName();
        }
        return $result;
    }

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @return mixed|null
     */
    private function getChildProduct($subject)
    {
        if ($subject->getProductType() !== 'configurable') {
            return null;
        }
        if ($simpleOption = $subject->getOptionByCode('simple_product')) {
            return $simpleOption->getProduct();
        }
        return null;
    }
}
