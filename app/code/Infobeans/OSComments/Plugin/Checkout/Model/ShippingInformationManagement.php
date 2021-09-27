<?php

/**
 * InfoBeans Sales Shipment Comment Extension
 *
 * @category   Infobeans
 * @package    Infobeans_OSComments
 * @version    2.0.0
 * @description Override template to show Pre-Order Note
 *
 * Release with version 2.0.0
 *
 * @author     InfoBeans Technologies Limited http://www.infobeans.com/
 * @copyright  Copyright (c) 2017 InfoBeans Technologies Limited
 */

namespace Infobeans\OSComments\Plugin\Checkout\Model;

class ShippingInformationManagement
{
    // @codingStandardsIgnoreLine
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    // @codingStandardsIgnoreLine
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();
        $deliveryComment = htmlspecialchars($extAttributes->getDeliveryComment());
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setDeliveryComment($deliveryComment);
    }
}
