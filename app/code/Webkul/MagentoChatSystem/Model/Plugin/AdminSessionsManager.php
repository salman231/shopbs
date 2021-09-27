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
namespace Webkul\MagentoChatSystem\Model\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Security\Model\AdminSessionsManager as SessionsManager;
use Webkul\MagentoChatSystem\Model\AgentDataFactory;
use Webkul\MagentoChatSystem\Model\AssignedChatFactory;
use Webkul\MagentoChatSystem\Model\TotalAssignedChatFactory;

/**
 * Magento\Backend\Model\Auth\Session decorator
 */
class AdminSessionsManager
{
    /**
     * @var AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @var AssignedChatFactory
     */
    protected $assignedChatFactory;

    /**
     * @var TotalAssignedChatFactory
     */
    protected $totalAssignedChatFactory;

    /**
     * @param AgentDataFactory $agentDataFactory
     * @param AssignedChatFactory $assignedChatFactory
     * @param TotalAssignedChatFactory $totalAssignedChatFactory
     */
    public function __construct(
        AgentDataFactory $agentDataFactory,
        AssignedChatFactory $assignedChatFactory,
        TotalAssignedChatFactory $totalAssignedChatFactory
    ) {
        $this->agentDataFactory = $agentDataFactory;
        $this->assignedChatFactory = $assignedChatFactory;
        $this->totalAssignedChatFactory = $totalAssignedChatFactory;
    }

    /**
     * Admin Session prolong functionality
     *
     * @param Session $session
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundProcessLogout(SessionsManager $session, \Closure $proceed)
    {
        $user = $session->getCurrentSession();
        $agentModel = $this->agentDataFactory->create();

        $agentModelCollection = $this->agentDataFactory->create()->getCollection()
        ->addFieldToFilter('agent_id', ['eq' => $user->getUserId()]);
        if ($agentModelCollection->getSize()) {
            $entityId = $agentModelCollection->getLastItem()->getEntityId();
            $model = $agentModel->load($entityId);
            $model->setChatStatus(0);
            $model->setId($entityId)->save();
        }
        $this->removeAssignedAgent($user);

        $result = $proceed();
        return $result;
    }

    /**
     * Remove Assigned Agent
     *
     * @param object $user
     * @return void
     */
    protected function removeAssignedAgent($user)
    {
        $assignedAgent = $this->assignedChatFactory->create()->getCollection()
        ->addFieldToFilter('agent_id', ['eq' => $user->getUserId()])
        ->addFieldToFilter('chat_status', ['eq' => 1]);

        if ($assignedAgent->getSize()) {
            $agentId = $assignedAgent->getLastItem()->getAgentId();

            foreach ($assignedAgent as $assigned) {
                $assigned->setChatStatus(0);
                $assigned->setId($assigned->getEntityId());
                $assigned->save();
            }

            $totalAssignedChat = $this->totalAssignedChatFactory->create()->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $agentId]);
            $totalAssignId = $totalAssignedChat->getFirstItem()->getEntityId();
            $totalActiveChat = $totalAssignedChat->getFirstItem()->getTotalActiveChat();
            $totalAssignedModel = $this->totalAssignedChatFactory->create()->load($totalAssignId);
            $totalAssignedModel->setTotalActiveChat($totalActiveChat-1);
            $totalAssignedModel->setId($totalAssignId)->save();
        }
    }
}
