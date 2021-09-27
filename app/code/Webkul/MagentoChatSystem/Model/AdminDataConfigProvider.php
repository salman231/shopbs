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

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Webkul\MagentoChatSystem\Model\AgentDataFactory;

class AdminDataConfigProvider
{

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Webkul\MagentoChatSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    private $adminHelper;

    /**
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    private $aclRetriever;

    /**
     * @var AgentDataFactory
     */
    private $agentDataFactory;

    /**
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param FormKey $formKey
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\MagentoChatSystem\Helper\Data $helper
     * @param \Magento\Backend\Helper\Data $adminHelper
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     * @param AgentDataFactory $agentDataFactory
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        FormKey $formKey,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MagentoChatSystem\Helper\Data $helper,
        \Magento\Backend\Helper\Data $adminHelper,
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        AgentDataFactory $agentDataFactory
    ) {
        $this->authSession = $authSession;
        $this->formKey = $formKey;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->adminHelper = $adminHelper;
        $this->aclRetriever = $aclRetriever;
        $this->agentDataFactory = $agentDataFactory;
        $this->fileDriver = $fileDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $defaultImageUrl = $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ).
        'chatsystem/admin/default.png';

        $output['formKey'] = $this->formKey->getFormKey();
        $output['adminData'] = $this->getAdminData();
        $output['adminChatName'] = $this->helper->getConfigData('chat_config', 'chat_name');
        $output['isAdminLoggedIn'] = $this->isAdminLoggedIn();
        $output['isSuperAdmin'] = $this->isSuperAdmin();
        $output['isServerRunning'] = $this->isServerRunning();
        $output['host'] = $this->helper->getConfigData('chat_config', 'host_name');
        $output['port'] = $this->helper->getConfigData('chat_config', 'port_number');
        $output['adminBaseUrl'] = $this->adminHelper->getUrl('chatsystem/message/save');
        $output['adminUpdateChatUrl'] = $this->adminHelper->getUrl('chatsystem/chat/updatestatus');
        $output['removeAssignedChatUrl'] = $this->adminHelper->getUrl('chatsystem/chat/removesuperadmin');
        $output['AdminloadMsgUrl'] = $this->adminHelper->getUrl('chatsystem/message/loadhistory');
        $output['AdminclearMsgUrl'] = $this->adminHelper->getUrl('chatsystem/message/clearhistory');

        if ($this->helper->getConfigData('chat_config', 'admin_image')) {
            $output['adminImage'] = $this ->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).
            'chatsystem/admin/'.
            $this->helper->getConfigData('chat_config', 'admin_image');
        } else {
            $output['adminImage'] = $defaultImageUrl;
        }
        
        $output['defaultImageUrl'] = $defaultImageUrl;

        return $output;
    }

    /**
     * Retrieve customer data
     *
     * @return array
     */
    private function getAdminData()
    {
        $adminData = [];
        $id = $this->authSession->getUser()->getId();
        if ($this->isAdminLoggedIn()) {
            $adminData['name'] = $this->authSession->getUser()->getName();
            $adminData['email'] = $this->authSession->getUser()->getEmail();
            $adminData['id'] = $this->authSession->getUser()->getId();

            $agentModelCollection = $this->agentDataFactory->create()->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $this->authSession->getUser()->getId()]);

            $agentStatus = $agentModelCollection->getLastItem()->getChatStatus();
            $adminData['status'] = $agentStatus;
            $adminData['agent_unique_id'] = $agentModelCollection->getLastItem()->getAgentUniqueId();
        }
        return $adminData;
    }

    /**
     * Check Super Admin
     *
     * @return boolean
     */
    protected function isSuperAdmin()
    {
        $id = $this->authSession->getUser()->getId();
        if ($this->isAdminLoggedIn()) {
            $userRole = $this->authSession->getUser()->getRole();
            $resources = $this->aclRetriever->getAllowedResourcesByRole($userRole->getId());
            if ($userRole->getRoleName() == 'Administrators' &&
                in_array('Magento_Backend::all', $resources)
            ) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Check Server Running
     *
     * @return boolean
     */
    public function isServerRunning()
    {
        $host = $this->helper->getConfigData('chat_config', 'host_name');
        $port = $this->helper->getConfigData('chat_config', 'port_number');
        try {
            $connection = fsockopen($host, $port);
            if (is_resource($connection)) {
                $result = true;
                $this->fileDriver->fileClose($connection);
            } else {
                $result = false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return $result;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isAdminLoggedIn()
    {
        return (bool)$this->authSession->isLoggedIn();
    }
}
