<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infobeans\OSComments\Controller\Multishipping\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

class ShippingPost extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * @return void
     */
    public function execute()
    {
        $shippingMethods = $this->getRequest()->getPost('shipping_method');
        // infobeans os-comments for multiple shipping
        $deliveryComment = $this->getRequest()->getPost('delivery_comment');
        try {
            $this->_eventManager->dispatch(
                'checkout_controller_multishipping_shipping_post',
                ['request' => $this->getRequest(), 'quote' => $this->_getCheckout()->getQuote()]
            );
            
            // infobeans os-comments for multiple shipping comments save
            if ($deliveryComment) {
                $addresses = $this->_getCheckout()->getQuote()->getAllShippingAddresses();
                foreach ($addresses as $addresses) {
                    $addresses->setDeliveryComment($deliveryComment[$addresses->getId()]);
                }
            }
            // ends
            $this->_getCheckout()->setShippingMethods($shippingMethods);
            $this->_getState()->setActiveStep(State::STEP_BILLING);
            $this->_getState()->setCompleteStep(State::STEP_SHIPPING);
            $this->_redirect('*/*/billing');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/shipping');
        }
    }
}
