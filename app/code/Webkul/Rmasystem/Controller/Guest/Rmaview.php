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

class Rmaview extends \Magento\Framework\App\Action\Action
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

    protected $_customerSession;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param SessionManager $session
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        SessionManager $session,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Rmasystem\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->session = $session;
        $this->_customerSession = $customerSession;
        $this->helper = $helper;
        parent::__construct($context);
    }
    /**
     * Guest Rma View
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultPage = $this->resultPageFactory->create();
        if (!$this->_customerSession->getCustomer()->getId()) {
            $guestSession = $this->session->getGuestData();
            if ($guestSession['email'] == '') {
                $this->messageManager->addError(
                    __('You are not authorized to View RMA.')
                );
                return $resultRedirect->setPath('*/guest/login');
            }
            $this->coreRegistry->register('guest_data', $guestSession);
        } else {
            return $resultRedirect->setPath('rmasystem/index/index/');
        }
        return $resultPage;
    }
}
