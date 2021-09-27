<?php

namespace Magemonkey\Cancleorder\Observer;
use Magento\Framework\Event\ObserverInterface;

class CheckLoginPersistentObserver implements ObserverInterface
{
     /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $collectionFactory

    ) {

        $this->_customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->_collectionFactory = $collectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        $controller = $observer->getControllerAction();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlInterface = $objectManager->get('Magento\Framework\UrlInterface');
        $url = $urlInterface->getUrl('customer/account/login');

        $customerid = $this->_customerSession->getCustomer()->getId();
        $collection = $this->_collectionFactory->create()
                ->addFieldToFilter('seller_id', $customerid);
        if(count($collection) == 0 ) {
                // return $this;
            
        }else{
            if($controller != 'marketplace'){
                // $this->redirect->redirect($controller->getResponse(), 'marketplace/account/dashboard');
                // exit;
                // $observer->getControllerAction()->getResponse()->setRedirect($url);
            }
        }             
    }
}