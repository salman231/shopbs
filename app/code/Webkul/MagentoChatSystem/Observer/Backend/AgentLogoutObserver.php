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
use Magento\Security\Model\AdminSessionsManager;

/**
 * User backend observer model for passwords
 */
class AgentLogoutObserver implements ObserverInterface
{
    /**
     * @var AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var \Magento\Security\Model\AdminSessionInfoFactory
     */
    protected $adminSessionInfo;

    /**
     * @var Webkul\MagentoChatSystem\Model\AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @param AdminSessionsManager $sessionsManager
     * @param \Magento\Security\Model\AdminSessionInfoFactory $adminSessionInfo
     * @param Webkul\MagentoChatSystem\Model\AgentDataFactory $agentDataFactory
     */
    public function __construct(
        AdminSessionsManager $sessionsManager,
        \Magento\Security\Model\AdminSessionInfoFactory $adminSessionInfo,
        \Webkul\MagentoChatSystem\Model\AgentDataFactory $agentDataFactory
    ) {
        $this->sessionsManager = $sessionsManager;
        $this->adminSessionInfo = $adminSessionInfo;
        $this->agentDataFactory = $agentDataFactory;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /* @var $user \Magento\User\Model\User */
        $user = $this->sessionsManager->getCurrentSession();
        $isSessionExpired = $this->adminSessionInfo->create()->isSessionExpired();
        
        if ($isSessionExpired) {
            if ($user->getUserId()) {
                $agentModel = $this->agentDataFactory->create();

                $agentModelCollection = $this->agentDataFactory->create()->getCollection()
                ->addFieldToFilter('agent_id', ['eq' => $user->getUserId()]);
                if ($agentModelCollection->getSize()) {
                    $entityId = $agentModelCollection->getLastItem()->getEntityId();
                    $model = $agentModel->load($entityId);
                    $model->setChatStatus(0);
                    $model->setId($entityId)->save();
                }
            }
        }
    }
}
