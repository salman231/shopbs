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

use Webkul\MagentoChatSystem\Api\SaveCustomerInterface;
use Webkul\MagentoChatSystem\Model\CustomerDataRepository as CustomerDataRepository;
use Webkul\MagentoChatSystem\Api\Data\CustomerDataInterfaceFactory;
use Webkul\MagentoChatSystem\Model\ResourceModel\CustomerData\CollectionFactory;
use Webkul\MagentoChatSystem\Model\ResourceModel\AssignedChat\CollectionFactory as AssignedChatCollection;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MagentoChatSystem\Api\AgentDataRepositoryInterface;
use Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface;

class SaveCustomer implements SaveCustomerInterface
{
    /**
     * @var CustomerDataRepository
     */
    protected $dataRepository;

    /**
     * @var CollectionFactory
     */
    protected $_dataCollection;

    /**
     * @var AssignedChatCollection
     */
    protected $assignedChatCollection;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomerDataInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param CustomerDataRepository $dataRepository
     * @param CustomerDataInterfaceFactory $customerDataFactory
     * @param CollectionFactory $dataCollection
     * @param AssignedChatCollection $assignedChatCollection
     * @param AgentDataRepositoryInterface $agentRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MagentoChatSystem\Helper\Data $helper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        CustomerDataRepository $dataRepository,
        CustomerDataInterfaceFactory $customerDataFactory,
        CollectionFactory $dataCollection,
        AssignedChatCollection $assignedChatCollection,
        AgentDataRepositoryInterface $agentRepository,
        DataObjectHelper $dataObjectHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MagentoChatSystem\Helper\Data $helper,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->dataRepository = $dataRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->_dataCollection = $dataCollection;
        $this->assignedChatCollection = $assignedChatCollection;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerSession = $customerSession;
        $this->agentRepository = $agentRepository;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $email Users name.
     * @param int $agentId Agent Id.
     * @param string $agentUniqueId Users name.
     * @return string Greeting message with users name.
     */
    public function save($message, $agentId, $agentUniqueId)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        if (!$customerId) {
            $customerId = $this->helper->getCustomerId();
        }
        $customer = $this->customerFactory->create()->load($customerId);

        if ($customer) {
            $defaultImageUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).
            'chatsystem/default.png';

            $chatCustomerCollection = $this->_dataCollection->create()
                ->addFieldToFilter('customer_id', ['eq' => $customer->getId()]);

            if ($chatCustomerCollection->getSize()) {
                $entityId = 0;
                foreach ($chatCustomerCollection as $dataCollection) {
                    $entityId = $dataCollection->getEntityId();
                    $uniqueId = $dataCollection->getUniqueId();
                }

                $assignedAgentCollection = $this->assignedChatCollection->create()
                    ->addFieldToFilter('customer_id', ['eq' => $customerId])
                    ->addFieldToFilter('chat_status', ['eq' => 1]);
                
                $savedData = $this->dataRepository->getById($entityId);
                $savedData = (array) $savedData->getData();
                $customerData = array_merge(
                    $savedData,
                    ['customer_id' => $customer->getId(),'chat_status' => 1]
                );
                $customerData['alreadyAssigned'] = false;
                if ($assignedAgentCollection->getSize()) {
                    $assignedModel = $assignedAgentCollection->getLastItem();
                    $agentModel = $this->agentRepository->getByAgentId($assignedModel->getAgentId());
                    $customerData['alreadyAssigned'] = true;
                    $customerData['agent_unique_id'] = $agentModel->getAgentUniqueId();
                    $customerData['agent_id'] = $agentModel->getAgentId();
                    $customerData['agent_name'] =  $agentModel->getAgentName();
                    $customerData['email'] = $agentModel->getAgentEmail();
                }
                if ($chatCustomerCollection->getLastItem()->getImage()) {
                    $defaultImageUrl = $this ->storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ).
                    'chatsystem/profile/'
                    .$customer->getId().'/'.$chatCustomerCollection->getLastItem()->getImage();
                }
                
                $customerData['entity_id'] = $entityId;
                $customerData['unique_id'] = $uniqueId;
            } else {
                $customerData = [
                    'customer_id' => $customer->getId(),
                    'chat_status' => 1,
                    'unique_id' => $this->generateUniqueId(),
                    'alreadyAssigned' => false
                ];
            }
            $dataObject = $this->customerDataFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $dataObject,
                $customerData,
                CustomerDataInterface::class
            );
            $customerData['customer_name'] = $customer->getName();
            $customerData['customer_email'] = $customer->getEmail();
            $customerData['profileImageUrl'] = $defaultImageUrl;
            try {
                $this->dataRepository->save($dataObject);
                $customerData['message'] = $message;
                $customerData['error'] = false;
            } catch (\Exception $e) {
                $customerData['error'] = true;
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
            return json_encode($customerData);
        }
    }
    
    /**
     * Generate unique id
     *
     * @return string
     */
    public function generateUniqueId()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $id = 'wk'.implode($pass);
        return $id;
    }
}
