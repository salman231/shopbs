<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MagentoChatSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
    
namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Agent;

class View extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Webkul_MagentoChatSystem::agents';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\MagentoChatSystem\Model\AgentDataFactory
     */
    protected $agentFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Webkul\MagentoChatSystem\Model\AgentDataFactory $agentFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Webkul\MagentoChatSystem\Model\AgentDataFactory $agentFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->agentFactory = $agentFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    public function execute()
    {
        $entityId = (int)$this->getRequest()->getParam('entity_id');
        if ($entityId) {
            $model = $this->agentFactory->create()->load($entityId);
            if ($model->getId()) {
                $this->_coreRegistry->register('agent_data', $model);
                $this->dataPersistor->set('agent_data', $model);
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('Webkul_MagentoChatSystem::agents');
                $resultPage->getConfig()->getTitle()->prepend(__('Chat Details'));
                return $resultPage;
            }
        }
        $this->messageManager->addException($e, __('Agent details does not exists.'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('chatsystem/agent/agentlist');
        return $resultRedirect;
    }
}
