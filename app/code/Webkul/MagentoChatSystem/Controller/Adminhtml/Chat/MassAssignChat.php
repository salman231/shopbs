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

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MagentoChatSystem\Model\ResourceModel\AssignedChat\CollectionFactory;
use Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface;
use Webkul\MagentoChatSystem\Model\Agent\AssignedChatRepository;
use Webkul\MagentoChatSystem\Model\Agent\AgentDataRepository;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassAssignChat extends Action
{
    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AgentDataRepository
     */
    protected $agentRepository;

    /**
     * @var AssignedChatRepository
     */
    protected $assignedChatRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    protected $assigned;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param Filter $filter
     * @param AssignedChatRepository $assignedChatRepository
     * @param AgentDataRepository $agentRepository
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        Filter $filter,
        AssignedChatRepository $assignedChatRepository,
        AgentDataRepository $agentRepository,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->agentRepository = $agentRepository;
        $this->assignedChatRepository = $assignedChatRepository;
        $this->date = $date;
        parent::__construct($context);
    }
    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $agentId = (int) $this->getRequest()->getParam('entity_id');
        
        try {
            $agentData = $this->agentRepository->getById($agentId);
            
            $postItems = [];
            foreach ($collection as $item) {
                $this->setAssigned($item);
                $postItems['agent_unique_id'] = $agentData->getAgentUniqueId();
                $postItems['agent_id'] = $agentData->getAgentId();
                $postItems['assigned_at'] = $this->date->gmtDate();
                
                $this->updateAssigned($postItems);
                $this->saveAssigned($this->getAssigned());
            }
            
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been updated.', $collection->getSize())
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException(
                $e,
                __('Something went wrong while updating the part finder(s) status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/agent/assignedchat');
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
}
