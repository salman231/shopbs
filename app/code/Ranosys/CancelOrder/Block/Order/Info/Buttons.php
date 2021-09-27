<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ranosys\CancelOrder\Block\Order\Info;

use Magento\Customer\Model\Context;

/**
 * Block of links in Order view page
 */
class Buttons extends \Magento\Sales\Block\Order\Info\Buttons
{

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Framework\App\Http\Context              $httpContext
     * @param \Ranosys\CancelOrder\Helper\Data                 $helper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Ranosys\CancelOrder\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $registry, $httpContext, $data);
    }

    /**
     * Get url for canceling order
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getFormAction($order)
    {
        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return '';
        }
        return $this->getUrl('sales/order/cancel', ['order_id' => $order->getId(), '_secure' => true]);
    }

    /*
     * Return module status
     */

    public function getEnable()
    {
        return $this->helper->getEnable();
    }

    /*
     * Return custom notice text
     */

    public function getNotice()
    {
        return $this->helper->getNotice();
    }

    /*
     * Return button text
     */

    public function getLabel()
    {
        return $this->helper->getLabel();
    }
}
