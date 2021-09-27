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

use Webkul\MagentoChatSystem\Helper\Data;
use Webkul\MagentoChatSystem\Model\AgentDataFactory;
use Webkul\MagentoChatSystem\Model\AssignedChatFactory;

class UpdateStatus extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Webkul\MagentoChatSystem\Data
     */
    protected $helper;

    /**
     * @var AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @var AssignedChatFactory
     */
    protected $assignedChatFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param AgentDataFactory $agentDataFactory
     * @param AssignedChatFactory $assignedChatFactory
     * @param Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        AgentDataFactory $agentDataFactory,
        AssignedChatFactory $assignedChatFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->agentDataFactory = $agentDataFactory;
        $this->assignedChatFactory = $assignedChatFactory;
        $this->helper = $helper;
    }
   
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $data = $this->getRequest()->getParam('formData');
        if (isset($data['adminId']) && $data['adminId'] !== '') {
            $chatCustomer = $this->agentDataFactory->create()
                ->getCollection()
                ->addFieldToFilter('agent_id', ['eq' => $data['adminId']]);
            $entityId = $chatCustomer->getLastItem()->getEntityId();
            $agentModel = $this->agentDataFactory->create()->load($entityId);
            $agentModel->setChatStatus($data['status']);
            $agentModel->setId($entityId);
            $agentModel->save();

            /*unassign all chats*/
            if ($data['status'] == 0) {
                $assignedChat = $this->assignedChatFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('agent_id', ['eq' => $data['adminId']])
                    ->addFieldToFilter('chat_status', ['eq' => 1]);

                if ($assignedChat->getSize()) {
                    foreach ($assignedChat as $assigned) {
                        $assigned->setChatStatus(0);
                        $assigned->setId($assigned->getEntityId());
                        $this->helper->saveObject($assigned);
                    }
                }
            }
            $response->setMessage(
                __('Status Updated.')
            );
        } else {
            $response->setError(true);
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
