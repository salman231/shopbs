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
namespace Bss\Simpledetailconfigurable\Model\Import\Preselect\Validator;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Config as EavConfig;

class Attribute
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var array
     */
    private $attributeOptions = [];

    /**
     * @var array
     */
    private $skuList = [];

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param EavConfig $eavConfig
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        EavConfig $eavConfig
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param array $data
     * @return array|bool
     */
    public function validatePreselect($data)
    {
        $result = ['product_id' => 0, 'value' => []];
        if ($pId = $this->validateSku($data['sku'])) {
            $result['product_id'] = $pId;
        } else {
            return false;
        }
        $attributes = $this->decodeKey($data['preselect']);
        foreach ($attributes as $code => $value) {
            $attributeOptions = $this->getOptions($code);
            if (!$attributeOptions) {
                return false;
            }
            if (isset($attributeOptions['label'][$value])) {
                $result['value'][$attributeOptions['id']] = $attributeOptions['label'][$value];
            }
        }
        return $result;
    }

    /**
     * @param string $code
     * @return array
     */
    private function getOptions($code)
    {
        if (!isset($this->attributeOptions[$code])) {
            $this->attributeOptions[$code] = [];
            $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $code);
            if (!$attribute) {
                $this->attributeOptions[$code] = [];
            }
            $this->attributeOptions[$code]['id'] = $attribute->getId();
            $attribute->setStoreId(0);
            $allOptions = $attribute->getSource()->getAllOptions(false);
            foreach ($allOptions as $option) {
                $optionLabel = $option['label'];
                $this->attributeOptions[$code]['label'][$optionLabel] = $option['value'];
            }
        }
        return $this->attributeOptions[$code];
    }

    /**
     * @param string $sku
     * @return bool
     */
    private function validateSku($sku)
    {
        if (empty($this->skuList)) {
            $collection = $this->productCollectionFactory->create()
            ->addAttributeToFilter('type_id', 'configurable');
            foreach ($collection as $product) {
                $this->skuList[$product->getSku()] = $product->getId();
            }
        }
        return isset($this->skuList[$sku]) ? $this->skuList[$sku] : false;
    }

    /**
     * @param string $key
     * @return array
     */
    private function decodeKey($key)
    {
        $result = [];
        $attributes = explode(',', $key);
        foreach ($attributes as $attribute) {
            if (strpos($attribute, ':') !== false) {
                $value = explode(':', $attribute);
                $result[$value[0]] = $value[1];
            }
        }
        return $result;
    }
}
