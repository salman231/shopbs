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
namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Chat;

use Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface;
use Webkul\MagentoChatSystem\Model\Agent\AssignedChatRepository;
use Webkul\MagentoChatSystem\Model\Agent\AgentDataRepository;

class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Webkul_MagentoChatSystem::assigned';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    protected $assigned;

    /**
     * @var Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AssignedChatRepository
     */
    protected $assignedChatRepository;

    /**
     * @var AgentDataRepository
     */
    protected $agentRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param AssignedChatRepository $assignedChatRepository
     * @param AgentDataRepository $agentRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        AssignedChatRepository $assignedChatRepository,
        AgentDataRepository $agentRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->assignedChatRepository = $assignedChatRepository;
        $this->agentRepository = $agentRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->date = $date;
    }
   
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && !empty($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $assignedId) {
            $this->setAssigned($this->assignedChatRepository->getById($assignedId));
            $agentData = $this->agentRepository->getByAgentId($postItems[$assignedId]['agent_id']);
            $postItems[$assignedId]['agent_unique_id'] = $agentData->getAgentUniqueId();
            $postItems[$assignedId]['is_admin_chatting'] = 0;
            $postItems[$assignedId]['assigned_at'] = $this->date->gmtDate();
            $this->updateAssigned($postItems[$assignedId]);
            $this->saveAssigned($this->getAssigned());
        }
        return $resultJson->setData([
            'messages' => $this->getErrorMessages(),
            'error' => $this->isErrorExists()
        ]);
    }

    /**
     * Update assigned data
     *
     * @param array $data
     * @return void
     */
    protected function updateAssigned(array $data)
    {
        $assigned = $this->getAssigned();
        $assignedData = array_merge(
            $assigned->getData(),
            $data
        );
        $this->dataObjectHelper->populateWithArray(
            $assigned,
            $assignedData,
            AssignedChatInterface::class
        );
    }

    /**
     * Save customer with error catching
     *
     * @param AssignedChatInterface $assigned
     * @return void
     */
    protected function saveAssigned(AssignedChatInterface $assigned)
    {
        try {
            $this->assignedChatRepository->save($assigned);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->getMessageManager()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->getMessageManager()->addError(__('We can\'t assigned the chat.'));
        }
    }

    /**
     * Set assigned
     *
     * @param AssignedChatInterface $assigned
     * @return $this
     */
    protected function setAssigned(AssignedChatInterface $assigned)
    {
        $this->assigned = $assigned;
        return $this;
    }

    /**
     * Receive assigned
     *
     * @return AssignedChatInterface
     */
    protected function getAssigned()
    {
        return $this->assigned;
    }

    /**
     * Check if errors exists
     *
     * @return bool
     */
    protected function isErrorExists()
    {
        return (bool)$this->getMessageManager()->getMessages(true)->getCount();
    }

    /**
     * Get array with errors
     *
     * @return array
     */
    protected function getErrorMessages()
    {
        $messages = [];
        foreach ($this->getMessageManager()->getMessages()->getItems() as $error) {
            $messages[] = $error->getText();
        }
        return $messages;
    }
}
