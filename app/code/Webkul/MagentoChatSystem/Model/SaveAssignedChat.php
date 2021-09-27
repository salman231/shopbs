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

use Webkul\MagentoChatSystem\Api\SaveAssignedChatInterface;
use Webkul\MagentoChatSystem\Api\Data\AssignedChatInterfaceFactory;
use Webkul\MagentoChatSystem\Api\AgentDataRepositoryInterface;
use Webkul\MagentoChatSystem\Model\Agent\AssignedChatRepository;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MagentoChatSystem\Model\ResourceModel\AssignedChat\CollectionFactory;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\CollectionFactory as AgentCollectionFactory;
use Webkul\MagentoChatSystem\Helper\Data;
use Magento\Security\Model\ConfigInterface;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentData as AgentResource;
use Webkul\MagentoChatSystem\Model\TotalAssignedChatFactory;
use Magento\User\Model\UserFactory;
use Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface;

class SaveAssignedChat implements SaveAssignedChatInterface
{
    /**
     * @var AssignedChatRepository
     */
    protected $assignedChatRepository;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AssignedChatInterfaceFactory
     */
    protected $assignedChatFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var CollectionFactory
     */
    private $dataCollection;

    /**
     * @var TotalAssignedChatFactory
     */
    protected $totalAssignedChatFactory;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @param AssignedChatRepository $assignedChatRepository
     * @param AssignedChatInterfaceFactory $assignedChatFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param CollectionFactory $dataCollection
     * @param AgentCollectionFactory $agentCollection
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory
     *         $adminSessionInfoCollectionFactory
     * @param ConfigInterface $securityConfig
     * @param AgentResource $resource
     * @param AgentDataRepositoryInterface $agentRepository
     * @param Data $helper
     * @param TotalAssignedChatFactory $totalAssignedChatFactory
     * @param UserFactory $userFactory
     */
    public function __construct(
        AssignedChatRepository $assignedChatRepository,
        AssignedChatInterfaceFactory $assignedChatFactory,
        DataObjectHelper $dataObjectHelper,
        CollectionFactory $dataCollection,
        AgentCollectionFactory $agentCollection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory $adminSessionInfoCollectionFactory,
        ConfigInterface $securityConfig,
        AgentResource $resource,
        AgentDataRepositoryInterface $agentRepository,
        Data $helper,
        TotalAssignedChatFactory $totalAssignedChatFactory,
        \Webkul\MagentoChatSystem\Model\ResourceModel\CustomerData\CollectionFactory $customerDataFactory,
        UserFactory $userFactory
    ) {
        $this->assignedChatRepository = $assignedChatRepository;
        $this->assignedChatFactory = $assignedChatFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerSession = $customerSession;
        $this->date = $date;
        $this->dataCollection = $dataCollection;
        $this->agentCollection = $agentCollection;
        $this->helper = $helper;
        $this->adminSessionInfoCollectionFactory = $adminSessionInfoCollectionFactory;
        $this->securityConfig = $securityConfig;
        $this->resource = $resource;
        $this->agentRepository = $agentRepository;
        $this->totalAssignedChatFactory = $totalAssignedChatFactory;
        $this->userFactory = $userFactory;
        $this->customerDataFactory = $customerDataFactory;
    }

    /**
     * Returns assigned response
     *
     * @api
     * @param int $customerId
     * @param string $uniqueId
     * @return string  agentassigned chat data.
     */
    public function assignChat($customerId, $uniqueId, $alreadyAssignedAgent = false)
    {
        $returnData = [];
        $assignedData['error'] = false;
        $freeAgentId = 0;
        if ($customerId) {
            $userDataColl = $this->customerDataFactory->create()
                                        ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            $userDataModel = $userDataColl->getFirstItem();
            $userDataModel->setEndchat(1);
            $userDataModel->save();
            $assigned = $this->assignedChatRepository->getByCustomerId($customerId);
            
            // if chat not assigned to any manager
            if (!$assigned->getId()) {
                $agentCollection = $this->agentCollection->create()
                    ->addFieldToFilter('chat_status', ['eq' => 1]);

                $freeAgentId = $this->findLessFreeAgent($agentCollection);
            } else {
                $agent = $this->agentRepository->getByAgentId($assigned->getAgentId());
                // if chat already assigned to agent
                if ($agent->getAgentType() == 2) {
                    $isAgentLoggedIn = $this->getAgentCurrentStatus($assigned->getAgentId());
                    if (!$isAgentLoggedIn) {
                        $agentCollection = $this->agentCollection->create()
                            ->addFieldToFilter('chat_status', ['eq' => 1]);

                        $enableAutoAssignment = (bool) $this->helper->getConfigData(
                            'chat_config',
                            'assign_automatic'
                        );
                        if (!$enableAutoAssignment) {
                            $agentCollection->addFieldToFilter(
                                'agent_type',
                                ['eq' => 1] //check for agent type manager
                            );
                        } else {
                            $agentCollection->addFieldToFilter(
                                'agent_type',
                                ['eq' => 2] //check for agent
                            );
                        }
                        $freeAgentId = $this->findLessFreeAgent($agentCollection);
                    } else {
                        $freeAgentId = $assigned->getAgentId();
                    }
                } elseif ($agent->getAgentType() == 1) {
                    //if chat already assigned to chat manager
                    $isAgentLoggedIn = $this->getAgentCurrentStatus($assigned->getAgentId());
                    if (!$isAgentLoggedIn) {
                        $enableAutoAssignment = (bool) $this->helper->getConfigData(
                            'chat_config',
                            'assign_automatic'
                        );
                        if ($enableAutoAssignment) {
                            $agentCollection = $this->agentCollection->create()
                                ->addFieldToFilter('chat_status', ['eq' => 1])
                                ->addFieldToFilter(
                                    'agent_type',
                                    ['eq' => 1] //check for agent type manager
                                );
                            $freeAgentId = $this->findLessFreeAgent($agentCollection);
                        } else {
                            $freeAgentId = 0;
                        }
                    } else {
                        $freeAgentId = $assigned->getAgentId();
                    }
                } else {
                    $freeAgentId = 0;
                }
            }
            // if any agent or manager available then remove chat from admin and assign the agent/manager
            if ($freeAgentId) {
                $agentCollection = $this->agentCollection->create()
                    ->addFieldToFilter('agent_id', ['eq' => $freeAgentId]);

                $assignChatCollection = $this->dataCollection->create()
                    ->addFieldToFilter(
                        'agent_unique_id',
                        $agentCollection->getFirstItem()->getAgentUniqueId()
                    )
                    ->addFieldToFilter('customer_id', $customerId);
                
                $this->removeChatFromAdmin($customerId);

                if ($assignChatCollection->getSize()) {
                    $entityId = $assignChatCollection->getFirstItem()->getEntityId();
                    $dataObject = $this->assignedChatFactory->create()->load($entityId);
                    $assignedData = $dataObject->getData();
                    $assignedData['chat_status'] = 1;
                } else {
                    $assignedData = [
                        'agent_id' => $freeAgentId,
                        'agent_unique_id' => $agentCollection->getFirstItem()->getAgentUniqueId(),
                        'customer_id' => $customerId,
                        'unique_id' => $uniqueId,
                        'chat_status' => 1,
                        'assigned_at' => $this->date->gmtDate()
                    ];
                    $dataObject = $this->assignedChatFactory->create();
                }

                $this->dataObjectHelper->populateWithArray(
                    $dataObject,
                    $assignedData,
                    AssignedChatInterface::class
                );
                $agentModel = $this->userFactory->create()->load($freeAgentId);

                $assignedData['agent_name'] = $agentModel->getFirstName().' '.$agentModel->getLastName();
                $assignedData['agent_email'] = $agentModel->getEmail();
                $assignedData['agent_status'] = $agentCollection->getFirstItem()->getChatStatus();
                $assignedData['agentRatings'] = $this->helper->getAgentRating($freeAgentId);
                try {
                    if ($dataObject->getAgentId()) {
                        $this->assignedChatRepository->save($dataObject);
                    }

                    $totalAssignedCollection = $this->totalAssignedChatFactory->create()->getCollection()
                    ->addFieldToFilter('agent_id', ['eq' => $freeAgentId]);

                    if ($totalAssignedCollection->getSize()) {
                        $entityId = $totalAssignedCollection->getFirstItem()->getEntityId();
                        $totalChat = $totalAssignedCollection->getFirstItem()->getTotalActiveChat()+1;
                        $totalAssignedModel = $this->totalAssignedChatFactory->create()->load($entityId);

                        $totalAssignedModel->setTotalActiveChat($totalChat);
                        $totalAssignedModel->setId($entityId)->save();
                    } else {
                        $totalAssignedModel = $this->totalAssignedChatFactory->create();
                        $totalAssignedModel->setAgentId($freeAgentId);
                        $totalAssignedModel->setAgentUniqueId($agentCollection->getFirstItem()->getAgentUniqueId());
                        $totalAssignedModel->setTotalActiveChat(1);
                        $totalAssignedModel->save();
                    }
                    $assignedData['error'] = false;
                } catch (\Exception $e) {
                    $assignedData['error'] = true;
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                }
            } else {
                // remove chat from agent/manager and assign to admin
                $this->removeChatFromAgent($customerId);

                $agentCollection = $this->agentCollection->create()
                                            ->addFieldToFilter('agent_type', ['eq' => 0])
                                            ->addFieldToFilter('chat_status', ['eq' => 1]);
                if ($agentCollection->getSize()) {
                    $agentCollection = $agentCollection->getFirstItem();
                } else {
                    $agentCollection = $this->agentCollection->create()
                                                ->addFieldToFilter('agent_type', ['eq' => 0])
                                                ->getFirstItem();
                }
                
                $assignChatCollection = $this->dataCollection->create()
                    ->addFieldToFilter('agent_unique_id', $agentCollection->getAgentUniqueId())
                    ->addFieldToFilter('customer_id', $customerId);
                
                $isAgentLoggedIn = $this->getAgentCurrentStatus($agentCollection->getAgentId());
                $chatStatus = 0;
                if ($isAgentLoggedIn) {
                    $chatStatus = 1;
                }

                if ($assignChatCollection->getSize()) {
                    $entityId = $assignChatCollection->getFirstItem()->getEntityId();
                    $dataObject = $this->assignedChatFactory->create()->load($entityId);
                    $assignedData = $dataObject->getData();
                    $assignedData['is_admin_chatting'] = 1;
                    $assignedData['chat_status'] = $chatStatus;
                } else {
                    $assignedData = [
                        'agent_id' => $agentCollection->getAgentId(),
                        'agent_unique_id' => $agentCollection->getAgentUniqueId(),
                        'customer_id' => $customerId,
                        'unique_id' => $uniqueId,
                        'is_admin_chatting' => 1,
                        'chat_status' => $chatStatus,
                        'created_at' => $this->date->gmtDate()
                    ];
                    $dataObject = $this->assignedChatFactory->create();
                }
                $this->dataObjectHelper->populateWithArray(
                    $dataObject,
                    $assignedData,
                    AssignedChatInterface::class
                );
                
                $agentModel = $this->userFactory->create()->load($agentCollection->getAgentId());

                $assignedData['agent_name'] = $agentModel->getFirstName().' '.$agentModel->getLastName();
                $assignedData['agent_email'] = $agentModel->getEmail();
                $assignedData['agent_status'] = $chatStatus;
                $assignedData['agentRatings'] = $this->helper->getAgentRating($agentModel->getId());
                try {
                    if ($dataObject->getAgentId()) {
                        $this->assignedChatRepository->save($dataObject);
                    }
                } catch (\Exception $e) {
                    $assignedData['error'] = true;
                    throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                }
                $assignedData['error'] = false;
                if (!$chatStatus) {
                    $assignedData['error'] = true;
                }
                
                $assignedData['message'] = __(
                    'All agents are offline, try after sometime.'
                );
                if ((bool)$this->helper->getConfigData('chat_config', 'offline_message')) {
                    $assignedData['message'] = __(
                        'Agents are offline, send message we will revert you soon.'
                    );
                }
            }
            
            return json_encode($assignedData);
        }
    }

    /**
     * Returns assigned response
     *
     * @api
     * @param int $customerId
     * @param string $uniqueId
     * @param int $receiverId
     * @param string $receiverUniqueId
     * @return string  agentassigned chat data.
     */
    public function assignmentCheck($customerId, $uniqueId, $receiverId, $receiverUniqueId)
    {
        $returnData = [];
        $assignedData['same_agent'] = true;
        if ($customerId) {
            $assigned = $this->assignedChatRepository->getByCustomerId($customerId);
            if ($assigned->getAgentUniqueId() != $receiverUniqueId) {
                return $this->assignChat($customerId, $uniqueId);
            }
        }
        return json_encode($assignedData);
    }

    /**
     * Remove Asigned chat to admin if Chat manager is online
     *
     * @param int $customerId
     * @return void
     */
    protected function removeChatFromAdmin($customerId)
    {
        $assignChatCollection = $this->dataCollection->create()
            ->addFieldToFilter('is_admin_chatting', 1)
            ->addFieldToFilter('customer_id', $customerId);
        foreach ($assignChatCollection as $assigned) {
            $assigned->delete();
        }
    }

    /**
     * Remove Asigned chat to admin if Chat manager is online
     *
     * @param int $customerId
     * @return void
     */
    protected function removeChatFromAgent($customerId)
    {
        $assignChatCollection = $this->dataCollection->create()
            ->addFieldToFilter('customer_id', $customerId);
        foreach ($assignChatCollection as $assigned) {
            $assigned->delete();
        }
    }

    /**
     * Algo to find free agent
     * @param   \Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\Collection $agentCollection
     * @return int
     */
    private function findLessFreeAgent($agentCollection)
    {
        $agentId = 0;
        $agentChatArray = [];
        if ($agentCollection->getSize()) {
            foreach ($agentCollection as $agent) {
                $totalChatModel = $this->totalAssignedChatFactory->create()->getCollection()
                ->addFieldToFilter('agent_unique_id', ['eq' => $agent->getAgentUniqueId()]);
                if ($totalChatModel->getSize()) {
                    $totalChat = $totalChatModel->getFirstItem()->getTotalActiveChat();
                    $agentChatArray[] = [
                        'agent_id' => $agent->getAgentId(),
                        'total_chat' => $totalChat
                    ];
                } else {
                    $agentId = $agent->getAgentId();
                }
            }
            if (count($agentChatArray) && !$agentId) {
                $sortArray = [];
                foreach ($agentChatArray as $value) {
                    if (empty($sortArray)) {
                        $sortArray = $value;
                    } else {
                        if ($sortArray['total_chat'] > $value['total_chat']) {
                            $sortArray = $value;
                        }
                    }
                }
                $agentId = $sortArray['agent_id'];
            }
        }
        if ($agentId && !$this->getAgentCurrentStatus($agentId)) {
            $agentId = 0;
        }
        return $agentId;
    }

    /**
     * get admin users data.
     *
     * @return array
     */
    protected function getAgentCurrentStatus($agentId)
    {
        if (!$agentId) {
            return false;
        }
        $ids = [$agentId];
        $status = false;
        $connection = $this->resource->getConnection();
        $gmtTimestamp = $this->date->gmtTimestamp();
        $sessionLifeTime = $this->securityConfig->getAdminSessionLifetime();
        $sessionCollection = $this->createAdminSessionInfoCollection();

        $sessionCollection2 = clone $sessionCollection;
        $sessionCollection2
        ->addFieldToFilter('user_id', ['in' => $ids])
            ->addFieldToFilter(
                'updated_at',
                ['gt' => $connection->formatDate($gmtTimestamp - $sessionLifeTime)]
            );
        $sessionCollection->getSelect()->order('user_id DESC')->distinct(true)->group('user_id');
        $sessionCollection2->getSelect()->order('user_id DESC')->distinct(true)->group('user_id');
        
        $loggedInIds = [];
        foreach ($sessionCollection2 as $sessionModel) {
            $loggedInIds[] = $sessionModel->getUserId();
        }
        
        foreach ($sessionCollection as $model) {
            if (!in_array($model->getUserId(), $loggedInIds) &&
                $model->getStatus() == 1 &&
                $model->getUserId() == $agentId
            ) {
                $model->setStatus(0)->save();
            } elseif (in_array($model->getUserId(), $loggedInIds)) {
                $status = $model->getStatus();
            }
        }
        if (!$status) {
            $agent = $this->agentRepository->getByAgentId($agentId);
            $agent->setChatStatus($status);
            $this->agentRepository->save($agent);
        }

        return $status;
    }

    /**
     * @return \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection
     * @since 100.1.0
     */
    protected function createAdminSessionInfoCollection()
    {
        return $this->adminSessionInfoCollectionFactory->create();
    }
}
