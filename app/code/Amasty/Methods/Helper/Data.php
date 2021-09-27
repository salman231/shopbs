<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ){
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getScopeValue($path, $scopeCode)
    {
        return $this->scopeConfig->getValue('amasty_methods/' . $path, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $scopeCode);
    }

    public function getDefaultGroupId()
    {
        return $this->scopeConfig->getValue(
            \Magento\Customer\Model\GroupManagement::XML_PATH_DEFAULT_ID
        );
    }
}