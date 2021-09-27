<?php

namespace Magemonkey\Cancleorder\Controller\Magento\Checkout\Cart;

use Magento\Sales\Api\OrderManagementInterface;

class Index extends \Magento\Checkout\Controller\Cart\Index
{
	
    /**
     * Shopping cart display action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
    	
    	if(isset($_GET['failed']) && $_GET['failed'] != ''){
	        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	        $registry = $objectManager->get('Magento\Framework\Registry');
	        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');
	        $orderids = $checkoutSession->getLastRealOrderId();
	        $order = $objectManager->create('\Magento\Sales\Model\Order')->loadByIncrementId($orderids);
	        // echo $order->getId()." ".$orderids;
	        // exit;
	        if($order->getId() != ''){
	        	// echo "sdfsdffds ".$orderids;
	        	// exit;
	        

	        $cart = $objectManager->get('Magento\Checkout\Model\Cart');

	        $items = $order->getItemsCollection();
	       
	        foreach ($items as $item) {
	            try {
	                $cart->addOrderItem($item);
	               
	            } catch (\Magento\Framework\Exception\LocalizedException $e) {
	                if ($objectManager->get('Magento\Checkout\Model\Session')->getUseNotice(true)) {
	                    $this->messageManager->addNotice($e->getMessage());
	                } else {
	                    $this->messageManager->addError($e->getMessage());
	                }
	            } catch (\Exception $e) {
	                $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));

	            }
	        }


	    	$cart->save();
	    	
	    	$objectManager->create('Magento\Sales\Api\OrderManagementInterface')->cancel($order->getId());
	    	$registry->register('isSecureArea','true');
			$order->delete();
			$registry->unregister('isSecureArea'); 
			}
	        // exit;
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Shopping Cart'));
        return $resultPage;
    }
	
}
	
	