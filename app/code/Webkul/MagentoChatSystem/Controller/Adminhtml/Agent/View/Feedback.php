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
    
namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Agent\View;

class Feedback extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Webkul_MagentoChatSystem::AgentRating_view';

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Webkul\MagentoChatSystem\Model\AgentDataFactory
     */
    protected $agentFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Webkul\MagentoChatSystem\Model\AgentDataFactory $agentFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\MagentoChatSystem\Model\AgentDataFactory $agentFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->agentFactory = $agentFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute()
    {
        $entityId = (int)$this->getRequest()->getParam('entity_id');
        $model = $this->agentFactory->create()->load($entityId);
        $this->_coreRegistry->register('agent_data', $model);
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
