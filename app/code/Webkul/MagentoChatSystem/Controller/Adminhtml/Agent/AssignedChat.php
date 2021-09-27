<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MagentoChatSystem
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
    
namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Agent;

class AssignedChat extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Webkul_MagentoChatSystem::assigned_update';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MagentoChatSystem::assigned_update');
        $resultPage->getConfig()->getTitle()->prepend(__('Assigned Chat'));
        return $resultPage;
    }
}
