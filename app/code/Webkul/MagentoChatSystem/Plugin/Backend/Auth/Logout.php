<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MagentoChatSystem
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */
namespace Webkul\MagentoChatSystem\Plugin\Backend\Auth;

class Logout
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
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Security\Model\AdminSessionsManager $sessionsManager,
        \Magento\Security\Model\AdminSessionInfoFactory $adminSessionInfo,
        \Webkul\MagentoChatSystem\Model\AgentDataFactory $agentDataFactory
    ) {
        $this->sessionsManager = $sessionsManager;
        $this->adminSessionInfo = $adminSessionInfo;
        $this->agentDataFactory = $agentDataFactory;
    }

    /**
     * @param \Magento\Backend\Controller\Adminhtml\Auth\Logout $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundExecute(
        \Magento\Backend\Controller\Adminhtml\Auth\Logout $subject,
        \Closure $proceed
    ) {
        try {
        /* @var $user \Magento\User\Model\User */
            $user = $this->sessionsManager->getCurrentSession();
            $isSessionExpired = $this->adminSessionInfo->create()->isSessionExpired();

            if ($isSessionExpired) {
                if ($user->getUserId()) {
                    $agentModel = $this->agentDataFactory->create();

                    $agentModelCollection = $this->agentDataFactory->create()->getCollection()
                        ->addFieldToFilter('agent_id', ['eq' => $user->getUserId()]);
                    if ($agentModelCollection->getSize()) {
                        $entityId = $agentModelCollection->getFirstItem()->getEntityId();
                        $model = $agentModel->load($entityId);
                        $model->setChatStatus(0);
                        $model->setId($entityId)->save();
                    }
                }
            }
        } catch (\Exception $ex) {
            $logger->info($ex->getMessage());
        }

        return $proceed();
    }
}
