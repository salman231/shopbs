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
namespace Bss\Simpledetailconfigurable\Helper;

use Magento\Framework\Exception\NoSuchEntityException;

class AdditionalInfoSaving
{
    /**
     * @var \Bss\Simpledetailconfigurable\Model\ResourceModel\PreselectKey
     */
    private $preselectKey;

    /**
     * @var \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory
     */
    private $productEnabledModuleFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productInfo;

    /**
     * @var \Bss\Simpledetailconfigurable\Model\ResourceModel\CustomUrl
     */
    private $customUrlResource;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * AdditionalInfoSaving constructor.
     * @param \Magento\Catalog\Model\ProductRepository $productInfo
     * @param ModuleConfig $moduleConfig
     * @param \Bss\Simpledetailconfigurable\Model\ResourceModel\PreselectKey $preselectKey
     * @param \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory $productEnabledModuleFactory
     * @param \Bss\Simpledetailconfigurable\Model\ResourceModel\CustomUrl $customUrlResource
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productInfo,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig,
        \Bss\Simpledetailconfigurable\Model\ResourceModel\PreselectKey $preselectKey,
        \Bss\Simpledetailconfigurable\Model\ProductEnabledModuleFactory $productEnabledModuleFactory,
        \Bss\Simpledetailconfigurable\Model\ResourceModel\CustomUrl $customUrlResource
    ) {
        $this->productEnabledModuleFactory = $productEnabledModuleFactory;
        $this->preselectKey = $preselectKey;
        $this->productInfo = $productInfo;
        $this->customUrlResource = $customUrlResource;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param array $postData
     * @param int $productId
     */
    public function savePreselectKey($postData, $productId)
    {
        $this->preselectKey->deleteOldKey($productId);
        foreach ($postData['sdcp_preselect'] as $key => $value) {
            $this->preselectKey->savePreselectKey($productId, $key, $value);
        }
    }

    /**
     * @param int $productId
     * @param array $data
     */
    public function saveEnabledModuleOnProduct($productId, $data)
    {
        $this->productEnabledModuleFactory->create()->getResource()->deleteOldKey($productId);
        $this->productEnabledModuleFactory->create()->getResource()
        ->saveEnabled($productId, $data['enabled'], $data['is_ajax_load']);
        if ($data['is_ajax_load']) {
            $this->generateCustomUrl($productId);
        } else {
            $this->customUrlResource->deleteById($productId);
        }
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function generateCustomUrl($productId)
    {
        try {
            $parent = $this->productInfo->getById($productId);
            $parentUrl = $parent->getUrlKey();
            $typeInstance = $parent->getTypeInstance();
            if (get_class($typeInstance) == 'Magento\Catalog\Model\Product\Type\Simple' || get_class($typeInstance) == 'Magento\Bundle\Model\Product\Type') 
            {
                return null;
            }
            $parentAttribute = $parent->getTypeInstance()->getConfigurableAttributes($parent);
            $data = [];
            $result = [];
            $targetUrl = $parentUrl . $this->moduleConfig->getSuffix();
            foreach ($parentAttribute as $attrKey => $attrValue) {
                $attrCode = $attrValue->getProductAttribute()->getAttributeCode();
                $data[$attrKey] = [
                    'code' => $attrCode,
                    'values' => []
                ];
                foreach ($parent->getAttributes()[$attrCode]->getOptions() as $tvalue) {
                    $data[$attrKey]['values'][$tvalue->getValue()] = $tvalue->getLabel();
                }
            }

            $childIds = $parent->getTypeInstance()->getChildrenIds($productId);

            foreach ($childIds[0] as $childId) {
                $child = $this->productInfo->getById($childId);
                $childUrl = $parentUrl;
                foreach ($data as $attrKey => $attrValue) {
                    $value = $child->getData($attrValue['code']);
                    $childUrl .= '+' . $attrValue['code'] . '-' . $attrValue['values'][$value];
                }
                $childUrl = str_replace(' ', '~', $childUrl);

                $result[] = [
                    'product_id' => $productId,
                    'custom_url' => $childUrl,
                    'parent_url' => $targetUrl
                ];
            }
            $this->customUrlResource->deleteByUrl($targetUrl);
            $this->customUrlResource->updateUrl($result);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @return void
     */
    public function updateCustomUrlData()
    {
        $productCollection = $this->productEnabledModuleFactory->create()->getCollection()
        ->getItemsByColumnValue('is_ajax_load', 1);
        foreach ($productCollection as $product) {
            $this->generateCustomUrl($product->getProductId());
        }
    }
}
