<?php

/**
 * InfoBeans Sales Shipment Comment Extension
 *
 * @category   Infobeans
 * @package    Infobeans_OSComments
 * @version    2.0.0
 *
 * Release with version 2.0.0
 *
 * @author     InfoBeans Technologies Limited http://www.infobeans.com/
 * @copyright  Copyright (c) 2017 InfoBeans Technologies Limited
 */

namespace Infobeans\OSComments\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XPATH_ENABLE = "infobeans_oscomments/general/enable_module";
    const XPATH_SHIPPING_LABEL = "infobeans_oscomments/general/shipping_label";
    const XPATH_SHOW_BLANK_COMMENT = "infobeans_oscomments/general/show_comment";

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    // @codingStandardsIgnoreLine
    protected $storeManager;
    
    /**
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     */
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }
    
    /**
     * @return store id
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    public function getShippingLabel()
    {
        return $this->getConfig(self::XPATH_SHIPPING_LABEL);
    }
    
    public function showBlankShippingComment()
    {
        return $this->getConfig(self::XPATH_SHOW_BLANK_COMMENT);
    }

    public function isModuleEnabled()
    {
        $enabled = $this->getConfig(self::XPATH_ENABLE);
        if ($enabled) {
            return true;
        }
        return false;
    }
}
