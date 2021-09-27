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
namespace Bss\Simpledetailconfigurable\Plugin\Layout;

class AddHandleProductLayout
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ProductData
     */
    private $productData;

    /**
     * @param \Bss\Simpledetailconfigurable\Helper\ProductData $productData
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\ProductData $productData,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->productData = $productData;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Add layout handle only when module enable
     * @param \Magento\Catalog\Helper\Product\View $subject
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @param \Magento\Catalog\Model\Product $product
     * @param null|\Magento\Framework\DataObject $params
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeInitProductLayout(
        \Magento\Catalog\Helper\Product\View $subject,
        \Magento\Framework\View\Result\Page $resultPage,
        $product,
        $params = null
    ) {
        if ($this->isEnabledSdcp($product)) {
            $resultPage->addHandle('bss_sdcp');
            $product->setIsEnabledSdcp(true);
        }
        return [$resultPage, $product, $params];
    }

    /**
     * @param $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isEnabledSdcp($product)
    {
        return $product->getTypeId() === 'configurable' &&
            $this->moduleConfig->isModuleEnable() &&
            $this->productData->getEnabledModuleOnProduct($product->getEntityId())['enabled'];
    }
}
