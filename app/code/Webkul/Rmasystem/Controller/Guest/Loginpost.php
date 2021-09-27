<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\SessionManager;

class Loginpost extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Session
     */
    protected $session;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        SessionManager $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * Guest Login
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        $post = $this->getrequest()->getPost();
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($post["order_id"]);
        $email = $order->getCustomerEmail();
        if ($email == $post["email"] && $order->getCustomerGroupId() == 0) {
            $this->messageManager->addSuccess(
                __('Login Successfully.')
            );
            $this->_objectManager->create('Magento\Framework\Session\SessionManager')->setGuestData($post);
            $this->coreRegistry->register('guest_data', $post);
            return $resultRedirect->setPath("*/guest/rmalist");
        } else {
            $this->_objectManager->create('Magento\Framework\Session\SessionManager')->unsetGuestData();
            $this->messageManager->addError(
                __('Invalid Credentials.')
            );
            return $resultRedirect->setPath('*/guest/login');
        }
    }
}
