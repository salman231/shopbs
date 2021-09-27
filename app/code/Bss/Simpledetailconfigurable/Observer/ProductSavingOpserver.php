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

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;

class ProductSavingOpserver implements ObserverInterface
{
    /**
     * @var \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving
     */
    private $additionalInfoSaving;

    /**
     * @var \Bss\Simpledetailconfigurable\Helper\ModuleConfig
     */
    private $moduleConfig;

    /**
     * ProductSavingOpserver constructor.
     * @param \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving $additionalInfoSaving
     * @param \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
     */
    public function __construct(
        \Bss\Simpledetailconfigurable\Helper\AdditionalInfoSaving $additionalInfoSaving,
        \Bss\Simpledetailconfigurable\Helper\ModuleConfig $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->additionalInfoSaving = $additionalInfoSaving;
    }

    /**
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $postData = $observer->getData('controller')->getRequest()->getPost('product');
        $productId = ($observer->getData('product')) ? $observer->getData('product')->getEntityId()
        : $postData['sdcp_preselect_id'];

        if ($this->moduleConfig->isModuleEnable() && array_key_exists('sdcp_preselect', $postData)) {
            $this->additionalInfoSaving->savePreselectKey($postData, $productId);
        }

        if ($this->moduleConfig->isModuleEnable() && array_key_exists('sdcp_general', $postData)) {
            $this->additionalInfoSaving->saveEnabledModuleOnProduct($productId, $postData['sdcp_general']);
        }
    }
}
