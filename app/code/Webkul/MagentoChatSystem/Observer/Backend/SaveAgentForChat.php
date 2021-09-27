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
class SaveAgentForChat implements ObserverInterface
{
    /**
     * @var Webkul\MagentoChatSystem\Model\AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    protected $aclRetriever;

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
        $user = $observer->getEvent()->getObject();

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
                $agentModel = $this->agentDataFactory->create();

                $agentModelCollection = $this->agentDataFactory->create()->getCollection()
                ->addFieldToFilter('agent_id', ['eq' => $user->getId()]);
                if ($agentModelCollection->getSize()) {
                    $entityId = $agentModelCollection->getLastItem()->getEntityId();
                    $model = $agentModel->load($entityId);
                    if ($userRole->getRoleName() == 'ChatSystem') {
                        $model->setAgentType(2);
                    } elseif ($userRole->getRoleName() == 'ChatManager') {
                        $model->setAgentType(1);
                    }
                    $model->setAgentEmail($user->getEmail());
                    $model->setAgentName($user->getFirstName(). ' '.$user->getLastName());
                    $model->setId($entityId)->save();
                } else {
                    $agentModel->setAgentId($user->getId());
                    $agentModel->setAgentUniqueId($this->generateUniqueId());
                    $agentModel->setAgentEmail($user->getEmail());
                    $agentModel->setAgentName($user->getFirstName(). ' '.$user->getLastName());
                    if ($userRole->getRoleName() == 'ChatSystem') {
                        $agentModel->setAgentType(2);
                    } elseif ($userRole->getRoleName() == 'ChatManager') {
                        $agentModel->setAgentType(1);
                    }
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
