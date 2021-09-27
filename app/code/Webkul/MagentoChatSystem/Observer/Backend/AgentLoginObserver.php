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

namespace Webkul\MagentoChatSystem\Observer\Backend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * User backend observer model for passwords
 */
class AgentLoginObserver implements ObserverInterface
{
    /**
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    protected $aclRetriever;

    /**
     * @var Webkul\MagentoChatSystem\Model\AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     * @param Webkul\MagentoChatSystem\Model\AgentDataFactory $agentDataFactory
     */
    public function __construct(
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever,
        \Webkul\MagentoChatSystem\Model\AgentDataFactory $agentDataFactory
    ) {
        $this->aclRetriever = $aclRetriever;
        $this->agentDataFactory = $agentDataFactory;
    }

    /**
     * Save current admin password to prevent its usage when changed in the future.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /* @var $user \Magento\User\Model\User */
        $user = $observer->getEvent()->getUser();

        if ($user->getId()) {
            $userRole = $user->getRole();
            $userRule = $user->getRules();
            $resources = $this->aclRetriever->getAllowedResourcesByRole($userRole->getId());
            if ($userRole->getRoleName() == 'ChatSystem' ||
                $userRole->getRoleName() == 'Administrators' ||
                $userRole->getRoleName() == 'ChatManager' ||
                in_array('Magento_Backend::all', $resources) ||
                in_array('Webkul_MagentoChatSystem::chatsystem', $resources)
            ) {
                $agentType = 0; // 0 for Admin
                $chatStatus = 1;
                if ($userRole->getRoleName() == 'ChatSystem') {
                    $agentType = 2; //2 for agent
                    $chatStatus = $user->getIsActive();
                }
                if ($userRole->getRoleName() == 'ChatManager') {
                    $agentType = 1; //1 for agent manager
                    $chatStatus = $user->getIsActive();
                }
                $agentModel = $this->agentDataFactory->create();

                $agentModelCollection = $this->agentDataFactory->create()->getCollection()
                ->addFieldToFilter('agent_id', ['eq' => $user->getId()]);
                
                if ($agentModelCollection->getSize()) {
                    $entityId = $agentModelCollection->getLastItem()->getEntityId();
                    $model = $agentModel->load($entityId);
                    $model->setChatStatus($chatStatus);
                    $model->setId($entityId)->save();
                } else {
                    $agentModel->setAgentId($user->getId());
                    $agentModel->setAgentUniqueId($this->generateUniqueId());
                    $agentModel->setAgentEmail($user->getEmail());
                    $agentModel->setAgentName($user->getFirstName(). ' '.$user->getLastName());
                    $agentModel->setChatStatus($chatStatus);
                    $agentModel->setAgentType($agentType);
                    $agentModel->save();
                }
            }
        }
    }

    /**
     * Generate Unique Id
     *
     * @return void
     */
    protected function generateUniqueId()
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
