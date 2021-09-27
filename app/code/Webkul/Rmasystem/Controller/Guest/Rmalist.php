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

class Rmalist extends \Magento\Framework\App\Action\Action
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

    protected $helper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        SessionManager $session,
        \Webkul\Rmasystem\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->session = $session;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Guest Rma List
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultPage = $this->resultPageFactory->create();
        if ($this->helper->isCustomerLoggedIn()) {
            return $resultRedirect->setPath('*');
        }
        $guestSession = $this->_objectManager->create('Magento\Framework\Session\SessionManager')->getGuestData();
        if ($guestSession['email'] == '') {
            $this->messageManager->addError(
                __('You are not authorized to view RMA list.')
            );
            return $resultRedirect->setPath('*/guest/login');
        }
        $this->coreRegistry->register('guest_data', $guestSession);
        return $resultPage;
    }
}
