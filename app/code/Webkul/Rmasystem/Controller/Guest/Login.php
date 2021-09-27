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

class Login extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        SessionManager $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     *
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultPage = $this->resultPageFactory->create();
        if ($this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('sales/order/history');
        }
        $alreadyLoggedIn = $this->session->getGuestData();
        if (isset($alreadyLoggedIn['email'])) {
            return $resultRedirect->setPath("*/guest/rmalist");
        }
        return $resultPage;
    }
}
