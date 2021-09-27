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
namespace Webkul\MagentoChatSystem\Helper;

use Magento\Security\Model\ConfigInterface;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentData as AgentResource;
use Webkul\MagentoChatSystem\Api\AgentDataRepositoryInterface;
use Webkul\MagentoChatSystem\Api\Data\AgentDataInterfaceFactory;
use Webkul\MagentoChatSystem\Model\AssignedChatFactory;
use Magento\Customer\Helper\View;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory;

/**
 * MpVendorAttributeManager data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterfaceFactory
     */
    protected $agentRatingFactory;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory
     */
    protected $adminSessionInfoCollectionFactory;

    /**
     * @var ConfigInterface
     */
    protected $securityConfig;

    /**
     * @var AgentResource
     */
    protected $resource;

    /**
     * @var AgentDataRepositoryInterface
     */
    protected $agentRepository;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var View
     */
    protected $viewHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterfaceFactory $agentRatingFactory
     * @param CollectionFactory $adminSessionInfoCollectionFactory
     * @param ConfigInterface $securityConfig
     * @param AgentResource $resource
     * @param AgentDataRepositoryInterface $agentRepository
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param AssignedChatFactory $assignedChatFactory
     * @param View $viewHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterfaceFactory $agentRatingFactory,
        CollectionFactory $adminSessionInfoCollectionFactory,
        ConfigInterface $securityConfig,
        AgentResource $resource,
        AgentDataRepositoryInterface $agentRepository,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        AssignedChatFactory $assignedChatFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        View $viewHelper
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->transportBuilder = $transportBuilder;
        $this->agentRatingFactory = $agentRatingFactory;
        $this->adminSessionInfoCollectionFactory = $adminSessionInfoCollectionFactory;
        $this->securityConfig = $securityConfig;
        $this->resource = $resource;
        $this->agentRepository = $agentRepository;
        $this->httpContext = $httpContext;
        $this->date = $date;
        $this->assignedChatFactory = $assignedChatFactory;
        $this->viewHelper = $viewHelper;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($group, $field)
    {
        $path = 'chatsystem/'.$group.'/'.$field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()
        );
    }

    /**
     * Send New customer Email
     *
     * @param object $customer
     * @param object $sender
     * @param array $templateParams
     * @param int $storeId
     * @return void
     */
    public function sendNewCustomerEmail(
        $customer,
        $sender,
        $templateParams = [],
        $storeId = null
    ) {
        $customerViewHelper = $this->viewHelper;
        $storeId = $this->storeManager->getStore()->getId();
        $email = $customer->getEmail();
        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($this->_scopeConfig->getValue(
                $sender,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            ))
            ->addTo($email, $customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();
    }
    
    /**
     * get total rating stars
     *
     * @return array
     */
    public function getAgentRating($agentId = null)
    {
        if (!$agentId) {
            $agentId = $this->getAgentId();
        }
        $ratingsTotal = [];
        $totalRatingFor1 = $this->agentRatingFactory->create()->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $agentId])
            ->addFieldToFilter('rating', ['eq' => 1])
            ->addFieldToFilter('status', ['eq' => 1])
            ->getSize();
        $totalRatingFor2 = $this->agentRatingFactory->create()->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $agentId])
            ->addFieldToFilter('rating', ['eq' => 2])
            ->addFieldToFilter('status', ['eq' => 1])
            ->getSize();
        $totalRatingFor3 = $this->agentRatingFactory->create()->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $agentId])
            ->addFieldToFilter('rating', ['eq' => 3])
            ->addFieldToFilter('status', ['eq' => 1])
            ->getSize();
        $totalRatingFor4 = $this->agentRatingFactory->create()->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $agentId])
            ->addFieldToFilter('rating', ['eq' => 4])
            ->addFieldToFilter('status', ['eq' => 1])
            ->getSize();
        $totalRatingFor5 = $this->agentRatingFactory->create()->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $agentId])
            ->addFieldToFilter('rating', ['eq' => 5])
            ->addFieldToFilter('status', ['eq' => 1])
            ->getSize();
        return $ratingsTotal = [
            '1' => $totalRatingFor1,
            '2' => $totalRatingFor2,
            '3' => $totalRatingFor3,
            '4' => $totalRatingFor4,
            '5' => $totalRatingFor5,
        ];
    }

    /**
     * get admin users data.
     *
     * @return array
     */
    public function getAgentCurrentStatus($agentId)
    {
        if (!$agentId) {
            return false;
        }
        $ids = [$agentId];
        $status = false;
        $connection = $this->resource->getConnection();
        $gmtTimestamp = $this->date->gmtTimestamp();
        $sessionLifeTime = $this->securityConfig->getAdminSessionLifetime();
        $sessionCollection = $this->createAdminSessionInfoCollection();
        $sessionCollection2 = clone $sessionCollection;
        $sessionCollection2
            ->addFieldToFilter('user_id', ['in' => $ids])
            ->addFieldToFilter(
                'updated_at',
                ['gt' => $connection->formatDate($gmtTimestamp - $sessionLifeTime)]
            );
        $sessionCollection->getSelect()->order('user_id DESC')->distinct(true)->group('user_id');
        $sessionCollection2->getSelect()->order('user_id DESC')->distinct(true)->group('user_id');
        
        $loggedInIds = [];
        foreach ($sessionCollection2 as $sessionModel) {
            $loggedInIds[] = $sessionModel->getUserId();
        }
        
        foreach ($sessionCollection as $model) {
            if (!in_array($model->getUserId(), $loggedInIds) &&
                $model->getStatus() == 1 &&
                $model->getUserId() == $agentId
            ) {
                $model->setStatus(0);
                $this->saveObject($model);
            } elseif (in_array($model->getUserId(), $loggedInIds)) {
                $status = $model->getStatus();
            }
        }
        if (!$status) {
            $agent = $this->agentRepository->getByAgentId($agentId);
            if ($agent && $agent->getId()) {
                $agent->setChatStatus($status);
                $this->agentRepository->save($agent);
            }
        }

        return $status;
    }

    /**
     * get agent id
     *
     * @return int
     */
    private function getAgentId()
    {
        $id = 0;
        $adminUserCollection = $this->assignedChatFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $this->customerSessionFactory->create()->getCustomerId()]);
        
        if ($adminUserCollection->getSize()) {
            return $adminUserCollection->getLastItem()->getAgentId();
        }
        return $id;
    }

    /**
     * @return \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection
     */
    protected function createAdminSessionInfoCollection()
    {
        return $this->adminSessionInfoCollectionFactory->create();
    }

    /**
     * function to get customer id from context
     *
     * @return int customerId
     */
    public function getCustomerId()
    {
        return $this->httpContext->getValue('chat_customer_id');
    }

    /**
     * function to get customer id from context
     *
     * @return int customerId
     */
    public function setCustomerId($customerId)
    {
        $this->httpContext->setValue(
            'chat_customer_id',
            $customerId,
            false
        );
    }

    /**
     * Save Object
     *
     * @param object $object
     * @return void
     */
    public function saveObject($object)
    {
        $object->save();
    }

    /**
     * Flush Cache
     */
    public function cacheFlush()
    {
        $types = ['full_page', 'block_html'];
        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
    }
}
