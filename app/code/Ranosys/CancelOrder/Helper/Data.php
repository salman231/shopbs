<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ranosys\CancelOrder\Helper;

/**
 * Ranosys CancelOrder Data Helper
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /*
     * Return module status
     */

    public function getEnable()
    {
        return $this->scopeConfig->getValue(
            'cancel_order_setting/general/module_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /*
     * Return custom notice text
     */

    public function getNotice()
    {
        return $this->scopeConfig->getValue(
            'cancel_order_setting/general/notice_txt',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /*
     * Return button text
     */

    public function getLabel()
    {
        return $this->scopeConfig->getValue(
            'cancel_order_setting/general/btn_label',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /*
     * Return sender email
     */

    public function getSender()
    {
        return $this->scopeConfig->getValue(
            'cancel_order_setting/general/sender_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /*
     * Return receiver email
     */

    public function getReceiver()
    {
        return $this->scopeConfig->getValue(
            'cancel_order_setting/general/receiver_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /*
     * Return email template
     */

    public function getTemplate()
    {
        return $this->scopeConfig->getValue(
            'cancel_order_setting/general/custom_email_template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
