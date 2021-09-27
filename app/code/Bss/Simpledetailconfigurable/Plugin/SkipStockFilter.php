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

class SkipStockFilter
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    private $moduleConfig;

    /**
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection $result
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return mixed
     */
    public function afterGetUsedProductCollection(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject,
        $result
    ) {
        if ($this->moduleConfig->isModuleEnable() && $this->moduleConfig->isShowStockStatus()) {
            $result->addAttributeToFilter('status', 1)->setFlag('has_stock_status_filter', false);
        }
        return $result;
    }
}
