<?php
namespace Magemonkey\Chargeanywhere\Observer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Webapi\Rest\Request as RestRequest;

class Paymentcapture implements ObserverInterface {
	public function execute(EventObserver $observer) {
		$paymentOrder = $observer->getEvent()->getInvoice()->getId();
		echo "<pre>";
		print_r($paymentOrder);
		exit;
	}
}