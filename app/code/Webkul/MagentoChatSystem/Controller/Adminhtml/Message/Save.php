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

use Webkul\MagentoChatSystem\Api\CustomerDataRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Webkul\MagentoChatSystem\Model\ResourceModel\AssignedChat\CollectionFactory as AssignedCollectionFactory;
use Webkul\MagentoChatSystem\Api\AgentDataRepositoryInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $encoder;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerDataRepositoryInterface
     */
    protected $customerDataRepository;

    /**
     * @var AgentDataRepositoryInterface
     */
    protected $agentRepository;

    /**
     * @var \Webkul\MagentoChatSystem\Model\MessageFactory
     */
    protected $messageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Url\EncoderInterface $encoder
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param CustomerFactory $customerFactory
     * @param CustomerDataRepositoryInterface $customerDataRepository
     * @param AssignedCollectionFactory $assignedCollectionFactory
     * @param AgentDataRepositoryInterface $agentRepository
     * @param \Webkul\MagentoChatSystem\Model\MessageFactory $messageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Url\EncoderInterface $encoder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        CustomerFactory $customerFactory,
        CustomerDataRepositoryInterface $customerDataRepository,
        AssignedCollectionFactory $assignedCollectionFactory,
        AgentDataRepositoryInterface $agentRepository,
        \Webkul\MagentoChatSystem\Model\MessageFactory $messageFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->encoder = $encoder;
        $this->date = $date;
        $this->customerFactory = $customerFactory;
        $this->customerDataRepository = $customerDataRepository;
        $this->agentRepository = $agentRepository;
        $this->messageFactory = $messageFactory;
    }
   
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $data = $this->getRequest()->getParam('formData');

        if (isset($data['senderId']) && isset($data['receiverId'])) {
            $chatCustomer = $this->customerDataRepository->getByCustomerId($data['receiverId']);
            $customerUniqueId = $chatCustomer->getUniqueId();

            if (isset($data['isSuperAdmin']) && $data['isSuperAdmin'] == true) {
                $assignedChatCollection = $this->assignedCollectionFactory->create()
                    ->addFieldToFilter('agent_id', ['eq' => $data['senderId']]);

                if (!$assignedChatCollection->getSize()) {
                    $assignedChatCollection = $this->assignedCollectionFactory->create();

                    $assignedChatCollection->setAgentId($data['senderId']);
                    $assignedChatCollection->setAgentUniqueId($data['senderUniqueId']);
                    $assignedChatCollection->setCustomerId($data['receiverId']);
                    $assignedChatCollection->setUniqueId($customerUniqueId);
                    $assignedChatCollection->setIsAdminChatting(1);
                    $assignedChatCollection->setChatStatus(1);
                    $assignedChatCollection->save();
                }
            }
            $agentData = $this->agentRepository->getByAgentId($data['senderId']);
            $customer = $this->customerFactory->create()->load($data['receiverId']);
            $message = trim($data['message']);
            if (isset($data['msg_type']) && ($data['msg_type'] == 'image' || $data['msg_type'] == 'file')) {
                $message = $this->encoder->encode(trim($data['message']));
            }

            $messageModel = $this->messageFactory->create();
            $messageModel->setSenderId($data['senderId']);
            $messageModel->setSenderUniqueId($data['senderUniqueId']);
            $messageModel->setSender($agentData->getAgentName());
            $messageModel->setReceiverId($data['receiverId']);
            $messageModel->setReceiverUniqueId($customerUniqueId);
            $messageModel->setReceiver($customer->getName());
            $messageModel->setMessage($message);
            $messageModel->setDate($this->date->gmtDate('Y-m-d H:i:s', $data['dateTime']));
            $messageModel->save();
            $response->setMessage($message);
        } else {
            $response->setError(true);
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
