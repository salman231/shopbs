<?php

namespace Infobeans\OSComments\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SaveOsCommentsMultiShipping implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return \Infobeans\OSComments\Observer\SaveOsComments
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $address = $observer->getAddress();
        $order->setDeliveryComment(htmlspecialchars($address->getDeliveryComment()));
        return $this;
    }
}
