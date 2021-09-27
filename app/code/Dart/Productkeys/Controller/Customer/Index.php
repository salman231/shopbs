<?php
/**
 * Dart Productkeys Customer Dashboard Controller.
 * @package   Dart_Productkeys
 *
 */

namespace Dart\Productkeys\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;

class Index extends Action
{
    public function __construct(Context $context, Session $customerSession, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        if (!$this->session->isLoggedIn()) {
            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath('customer/account/login');
            return $redirect;
        } else {
            $resultPage = $this->resultPageFactory->create();
            $this->_view->getPage()->getConfig()->getTitle()->set(__('My Keys'));
            return $resultPage;
        }
    }
}
