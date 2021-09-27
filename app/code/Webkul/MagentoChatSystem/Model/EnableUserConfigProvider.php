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

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Webkul\MagentoChatSystem\Api\CustomerDataRepositoryInterface;
use Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Customer\Model\ResourceModel\Online\Grid\CollectionFactory;
use Webkul\MagentoChatSystem\Model\AssignedChatFactory;

class EnableUserConfigProvider
{

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var SessionFactory
     */
    private $authSessionFactory;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var UrlInterface
     */
    protected $helper;

    /**
     * @var CustomerDataRepository
     */
    private $customerDataRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Authorization\Model\Acl\AclRetriever
     */
    protected $aclRetriever;

    /**
     * @var AssignedChatFactory
     */
    protected $assignedChatFactory;

    /**
     * @param \Magento\Backend\Model\Auth\SessionFactory $authSessionFactory
     * @param FormKey $formKey
     * @param \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever
     * @param CustomerDataRepositoryInterface $customerDataRepository
     * @param CustomerRepository $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param \Webkul\MagentoChatSystem\Helper\Data $helper
     * @param \Magento\Framework\View\Asset\Repository $viewFileSystem
     * @param CollectionFactory $onlineCustomerCollectionFactory
     * @param AssignedChatFactory $assignedChatFactory
     */
    public function __construct(
        \Magento\Backend\Model\Auth\SessionFactory $authSessionFactory,
        FormKey $formKey,
        \Magento\Authorization\Model\Acl\AclRetriever $aclRetriever,
        CustomerDataRepositoryInterface $customerDataRepository,
        CustomerRepository $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        \Webkul\MagentoChatSystem\Helper\Data $helper,
        \Magento\Framework\View\Asset\Repository $viewFileSystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $onlineCustomerCollectionFactory,
        AssignedChatFactory $assignedChatFactory
    ) {
        $this->authSessionFactory = $authSessionFactory;
        $this->formKey = $formKey;
        $this->aclRetriever = $aclRetriever;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->customerDataRepository = $customerDataRepository;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->viewFileSystem = $viewFileSystem;
        $this->onlineCustomerCollectionFactory = $onlineCustomerCollectionFactory;
        $this->assignedChatFactory = $assignedChatFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $output['formKey'] = $this->formKey->getFormKey();
        $output['enableUserData'] = $this->getEnableUsers();
        $output['isAdminLoggedIn'] = $this->isAdminLoggedIn();
        $output['host'] = $this->helper->getConfigData('config', 'host_name');
        $output['port'] = $this->helper->getConfigData('config', 'port_number');

        return $output;
    }

    /**
     * Retrieve customer data
     *
     * @return array
     */
    private function getEnableUsers()
    {
        $usersData = [];
        $id = $this->authSessionFactory->create()->getUser()->getId();
        if ($this->isAdminLoggedIn()) {
            $userRole = $this->authSessionFactory->create()->getUser()->getRole();
            $resources = $this->aclRetriever->getAllowedResourcesByRole($userRole->getId());
            $agentDataCollection = $this->assignedChatFactory->create()->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $id])
            ->addFieldToSelect('customer_id');
            
            $customerIds = [];
            $customerIds = $agentDataCollection->getData();
            $enabledUserFilter[] = $this->filterBuilder
                ->setField(CustomerDataInterface::CUSTOMER_ID)
                ->setConditionType('in')
                ->setValue($agentDataCollection->getData())
                ->create();

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilters($enabledUserFilter)
                ->create();
            
            $users = $this->customerDataRepository->getList($searchCriteria)->getItems();
            foreach ($users as $user) {
                $customer = $this->customerRepository->getById($user['customer_id']);
                $onlineCollection = $this->onlineCustomerCollectionFactory->create()
                    ->addFieldToFilter('customer_id', ['eq' => $customer->getId()]);
                
                // $user['chat_status'] = $onlineCollection->getSize()? 1: 0;
                if ($user['chat_status'] == 1 && $onlineCollection->getSize()) {
                    $statusClass = 'active';
                    $status = 1;
                } elseif ($user['chat_status'] == 2 && $onlineCollection->getSize()) {
                    $statusClass = 'busy';
                    $status = 2;
                } else {
                    $statusClass = 'offline';
                    $status = 0;
                }
                
                $defaultImageUrl = $this->viewFileSystem
                    ->getUrlWithParams('Webkul_MagentoChatSystem::images/default.png', []);
                $userImage = '';
                if (isset($user['image']) && $user['image'] != '') {
                    $userImage = $user['image'];
                    $image = $this->storeManager->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).
                    'chatsystem/profile/'
                    .$user['customer_id'].'/'.$userImage;
                } else {
                    $image  = $defaultImageUrl;
                }
                
                $usersData[] = [
                    'customerId' => $user['customer_id'],
                    'uniqueId'   => $user['unique_id'],
                    'customerName'          => $customer->getFirstname().' '.$customer->getLastname(),
                    'email'     => $customer->getEmail(),
                    'chat_status'   => $user['chat_status'],
                    'class' => $statusClass,
                    'image' => $image
                ];
            }
        }
        return $usersData;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isAdminLoggedIn()
    {
        return (bool)$this->authSessionFactory->create()->isLoggedIn();
    }
}
