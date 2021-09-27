<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Message;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\SessionManager;

class Index extends Action
{
     /**
      * @var \Magento\Framework\View\Result\PageFactory
      */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @var Magento\Framework\Session\SessionManager
     */
    protected $_session;

    /**
     * @param Context $context
     * @param SessionManager $session
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        SessionManager $session,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_session = $session;
    }

    /**
     * Seller list page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->_session->setAgentUniqueId($this->getRequest()->getParam('agent_unique_id'));
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MagentoChatSystem::assigned');
        $resultPage->getConfig()->getTitle()->prepend(__("Chat History"));
        return $resultPage;
    }

    /**
     * Check for is allowed.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MagentoChatSystem::assigned');
    }
}
