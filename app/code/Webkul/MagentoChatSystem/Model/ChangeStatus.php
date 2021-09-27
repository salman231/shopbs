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
namespace Webkul\MagentoChatSystem\Model;

use Webkul\MagentoChatSystem\Api\ChangeStatusInterface;
use Webkul\MagentoChatSystem\Model\CustomerDataRepository as CustomerDataRepository;
use Webkul\MagentoChatSystem\Api\Data\CustomerDataInterfaceFactory;
use Webkul\MagentoChatSystem\Model\ResourceModel\CustomerData\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface;

class ChangeStatus implements ChangeStatusInterface
{
    /**
     * @var Items
     */
    protected $dataRepository;

    /**
     * @var CollectionFactory
     */
    protected $_dataCollection;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomerDataInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Webkul\MagentoChatSystem\Model\TotalAssignedChatFactory
     */
    protected $totalAssignedChatFactory;

    /**
     * @var Webkul\MagentoChatSystem\Model\AssignedChatFactory
     */
    protected $assignedChatFactory;

    /**
     * @param CustomerDataRepository $dataRepository
     * @param CustomerDataInterfaceFactory $customerDataFactory
     * @param CollectionFactory $dataCollection
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Webkul\MagentoChatSystem\Model\AssignedChatFactory $assignedChatFactory
     * @param \Webkul\MagentoChatSystem\Model\TotalAssignedChatFactory $totalAssignedChatFactory
     */
    public function __construct(
        CustomerDataRepository $dataRepository,
        CustomerDataInterfaceFactory $customerDataFactory,
        CollectionFactory $dataCollection,
        DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\MagentoChatSystem\Model\AssignedChatFactory $assignedChatFactory,
        \Webkul\MagentoChatSystem\Model\TotalAssignedChatFactory $totalAssignedChatFactory
    ) {
        $this->dataRepository = $dataRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->_dataCollection = $dataCollection;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customerFactory = $customerFactory;
        $this->assignedChatFactory = $assignedChatFactory;
        $this->totalAssignedChatFactory = $totalAssignedChatFactory;
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $status Users name.
     * @return string Greeting message with users name.
     */
    public function changeStatus($status)
    {
        $customerId = $this->customerSessionFactory->create()->getCustomer()->getId();
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customer) {
            $chatCustomerCollection = $this->_dataCollection->create()
                ->addFieldToFilter('customer_id', ['eq' => $customer->getId()]);
            if ($status!=0) {
                $userColl = $chatCustomerCollection->getFirstItem();
                $userColl->setEndchat(1);
                $userColl->save();
            }
            $customerData = [];
            if ($chatCustomerCollection->getSize()) {
                $entityId = 0;
                $this->updateAssignedAgent($status);

                foreach ($chatCustomerCollection as $dataCollection) {
                    $entityId = $dataCollection->getEntityId();
                }
                $savedData = $this->dataRepository->getById($entityId);
                $savedData = (array) $savedData->getData();
                $customerData = array_merge(
                    $savedData,
                    ['customer_id' => $customer->getId(),'chat_status' => $status]
                );
                $customerData['entity_id'] = $entityId;
                
                $dataObject = $this->customerDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $dataObject,
                    $customerData,
                    CustomerDataInterface::class
                );
                
                try {
                    $this->dataRepository->save($dataObject);
                    $customerData['message'] = 'status changed';
                    $customerData['error'] = false;
                } catch (\Exception $e) {
                    $customerData['error'] = true;
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                }
            }
            return json_encode($customerData);
        }
    }

    /**
     * Remove Assigned Agent
     *
     * @return void
     */
    protected function updateAssignedAgent($status)
    {
        $customerId = $this->customerSessionFactory->create()->getCustomer()->getId();
        $assignedAgent = $this->assignedChatFactory->create()->getCollection()
        ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($status) {
            $assignedAgent->addFieldToFilter('chat_status', ['neq' => 1]);
        } else {
            $assignedAgent->addFieldToFilter('chat_status', ['eq' => 1]);
        }
        if ($assignedAgent->getSize()) {
            $assignedId = $assignedAgent->getLastItem()->getEntityId();
            $agentId = $assignedAgent->getLastItem()->getAgentId();

            $assignModel = $this->assignedChatFactory->create()->load($assignedId);
            $assignModel->setChatStatus($status);
            $assignModel->setId($assignedId)->save();

            $totalAssignedChat = $this->totalAssignedChatFactory->create()->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $agentId]);
            $totalAssignId = $totalAssignedChat->getLastItem()->getEntityId();
            $totalActiveChat = $totalAssignedChat->getLastItem()->getTotalActiveChat();
            $totalAssignedModel = $this->totalAssignedChatFactory->create()->load($totalAssignId);
            if ($status) {
                $totalActiveChat++;
            } else {
                $totalActiveChat--;
            }
            $totalAssignedModel->setTotalActiveChat($totalActiveChat);
            $totalAssignedModel->setId($totalAssignId)->save();
        }
    }
}
