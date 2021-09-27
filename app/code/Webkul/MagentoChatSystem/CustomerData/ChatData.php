<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MagentoChatSystem
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MagentoChatSystem\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Webkul\MagentoChatSystem\Api\CustomerDataRepositoryInterface;
use Webkul\MagentoChatSystem\Model\ResourceModel\CustomerData\CollectionFactory;
use Webkul\MagentoChatSystem\Model\ResourceModel\Message\CollectionFactory as MessageCollection;
use Webkul\MagentoChatSystem\Model\AgentDataFactory;
use Webkul\MagentoChatSystem\Api\AgentDataRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MagentoChatSystem\Model\AssignedChatFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem;

/**
 * ChatData source
 */
class ChatData extends \Magento\Framework\DataObject implements SectionSourceInterface
{

    /**
     * @var File
     */
    protected $file;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerUrlManager
     */
    protected $customerUrlManager;

    /**
     * @var CollectionFactory
     */
    protected $dataCollection;

    /**
     * @var CustomerDataRepositoryInterface
     */
    protected $customerDataRepository;

    /**
     * @var MessageCollection
     */
    protected $messageCollection;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Webkul\MagentoChatSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_viewFileSystem;

    /**
     * @var AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @var AgentDataRepositoryInterface
     */
    protected $agentRepository;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AssignedChatFactory
     */
    protected $assignedChatFactory;

    /**
     * @var CustomerSessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param CustomerRepository $customerRepository
     * @param CustomerUrlManager $customerUrlManager
     * @param CollectionFactory $dataCollection
     * @param CustomerDataRepositoryInterface $customerDataRepository
     * @param MessageCollection $messageCollection
     * @param HttpContext $httpContext
     * @param FormKey $formKey
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param \Webkul\MagentoChatSystem\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\View\Asset\Repository $viewFileSystem
     * @param AgentDataFactory $agentDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param AgentDataRepositoryInterface $agentRepository
     * @param AssignedChatFactory $assignedChatFactory
     * @param CustomerSessionFactory $customerSessionFactory
     * @param Filesystem $filesystem
     * @param File $file
     * @param array $data
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerUrlManager $customerUrlManager,
        CollectionFactory $dataCollection,
        CustomerDataRepositoryInterface $customerDataRepository,
        MessageCollection $messageCollection,
        HttpContext $httpContext,
        FormKey $formKey,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        \Webkul\MagentoChatSystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\View\Asset\Repository $viewFileSystem,
        AgentDataFactory $agentDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        AgentDataRepositoryInterface $agentRepository,
        AssignedChatFactory $assignedChatFactory,
        CustomerSessionFactory $customerSessionFactory,
        Filesystem $filesystem,
        File $file,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        array $data = []
    ) {
        parent::__construct($data);
        $this->customerRepository = $customerRepository;
        $this->customerUrlManager = $customerUrlManager;
        $this->dataCollection = $dataCollection;
        $this->customerDataRepository = $customerDataRepository;
        $this->messageCollection = $messageCollection;
        $this->httpContext = $httpContext;
        $this->formKey = $formKey;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->date = $date;
        $this->_viewFileSystem = $viewFileSystem;
        $this->agentDataFactory = $agentDataFactory;
        $this->agentRepository = $agentRepository;
        $this->urlDecoder = $urlDecoder;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->assignedChatFactory = $assignedChatFactory;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->fileDriver = $fileDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $this->customerSession = $this->customerSessionFactory->create();
        $output = [];
        $output['formKey'] = $this->formKey->getFormKey();
        $output['storeCode'] = $this->getStoreCode();
        $output['customerData'] = $this->getCustomerData();
        $output['adminChatName'] = $this->helper->getConfigData('chat_config', 'chat_name');
        $output['isCustomerLoggedIn'] = $this->isCustomerLoggedIn();
        $output['isServerRunning'] = $this->isServerRunning();
        $output['isAdminLoggedIn'] = $this->isAdminLoggedIn();
        $output['adminImage'] = $this->getAdminImage();
        $output['receiverId'] = $this->getAgentId();
        $output['receiverUniqueId'] = $this->getAgentUniqueId();
        $output['agentData'] = $this->getAgentData()->getData();
        $output['agentRating'] = $this->helper->getAgentRating();
        $output['superAdminData'] = $this->getSuperAdminData();
        $output['registerUrl'] = $this->getRegisterUrl();
        $output['host'] = $this->helper->getConfigData('chat_config', 'host_name');
        $output['port'] = $this->helper->getConfigData('chat_config', 'port_number');
        $output['notEnableOfflineChat'] = !((bool) $this->helper->getConfigData('chat_config', 'offline_message'));
        $output['openchatcount'] = 0;

        return $output;
    }

    /**
     * Creating customer data with messages history
     * @return array
     */
    private function getCustomerData()
    {
        $defaultImageUrl = $this->_viewFileSystem->getUrlWithParams('Webkul_MagentoChatSystem::images/default.png', []);
        $customerData = [];
        if (!$this->isCustomerLoggedIn()) {
            $customerData['profileImageUrl'] = $defaultImageUrl;
            return $customerData;
        }
        $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
        $customerData = $customer->__toArray();
        $chatCustomer = $this->customerDataRepository->getByCustomerId($customer->getId());
        
        if ($chatCustomer->getId()) {
            $customerData['chatStatus'] = $chatCustomer->getChatStatus();
            $customerData['profileImageUrl'] = $this ->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).
            'chatsystem/profile/'
            .$customer->getId().'/'.$chatCustomer->getImage();
            if ($chatCustomer->getImage() == '') {
                $customerData['profileImageUrl'] = $defaultImageUrl;
            }
            $customerData['uniqueId'] = $chatCustomer->getUniqueId();
            $customerUniqueId = $chatCustomer->getUniqueId();

            $loadDate = date("Y-m-d H:i:s");
            $loadDate = date('Y-m-d H:i:s', strtotime($loadDate . ' -7 day'));
            $message = $this->messageCollection->create()
                ->addFieldToFilter(
                    ['sender_unique_id', 'receiver_unique_id'],
                    [['eq' => $customerUniqueId], ['eq' => $customerUniqueId]]
                )
                ->addFieldToFilter('date', ['gteq'=> $loadDate])
                ->setOrder('date', 'ASC');
            $previousDate = '';
            
            foreach ($message as $key => $value) {
                $data = $value->getData();
                $changeDate = 0;
                $currentDate = strtotime($this->date->gmtDate('Y-m-d', $data['date']));
                if ($previousDate == '') {
                    $previousDate = strtotime($this->date->gmtDate('Y-m-d', $data['date']));
                    $changeDate = true;
                } elseif ($currentDate !== $previousDate) {
                    $changeDate = true;
                    $previousDate = strtotime($this->date->gmtDate('Y-m-d', $data['date']));
                }
                $data['type'] = 'text';

                $file = $this->urlDecoder->decode($data['message']);

                $fileSystem = $this->filesystem;
                $directory = $fileSystem->getDirectoryRead(DirectoryList::MEDIA);

                $fileName = 'chatsystem/attachments/' . ltrim($file, '/');
                $filePath = $directory->getAbsolutePath($fileName);
                
                if ($directory->isFile($fileName)) {
                    $paramType = 'image';
                    $info = $this->file->getPathInfo($filePath);
                    $extension = $info['extension'];
                    switch (strtolower($extension)) {
                        case 'gif':
                            $contentType = 'image/gif';
                            $data['type'] = 'image';
                            break;
                        case 'jpg':
                            $contentType = 'image/jpeg';
                            $data['type'] = 'image';
                            break;
                        case 'png':
                            $contentType = 'image/png';
                            $data['type'] = 'image';
                            break;
                        default:
                            $contentType = 'application/octet-stream';
                            $data['type'] = 'file';
                            $paramType = 'file';
                            break;
                    }
                    $data['message'] = $this->urlBuilder
                            ->getUrl(
                                'chatsystem/index/viewfile',
                                [$paramType => $data['message']]
                            );
                }
                
                $data['time'] = $this->date->gmtDate('h:i A', $data['date']);
                $data['date'] = $this->date->gmtDate('Y-m-d', $data['date']);
                $data['changeDate'] = $changeDate;
                $data['senderName'] = $this->getAgentByUniqueId($data['sender_unique_id']) ?
                $this->getAgentByUniqueId($data['sender_unique_id'])->getAgentName(): __('Support');
                $data['receiverName'] = $this->getAgentByUniqueId($data['receiver_unique_id']) ?
                $this->getAgentByUniqueId($data['receiver_unique_id'])->getAgentName(): __('Support');
                $customerData['messages'][$key] = $data;
            }
        } else {
            $customerData['chatStatus'] = 1;
            $chatCustomerCollection = $this->dataCollection->create()
                ->addFieldToFilter('customer_id', ['eq' => $customer->getId()]);
            $customerData['uniqueId'] = $chatCustomer->getUniqueId();
            $customerData['profileImageUrl'] = $defaultImageUrl;
        }
        return $customerData;
    }

    /**
     * Get chat server image
     *
     * @return string
     */
    protected function getAdminImage()
    {
        if ($this->helper->getConfigData('chat_config', 'admin_image')) {
            return $this ->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).
            'chatsystem/admin/'.
            $this->helper->getConfigData('chat_config', 'admin_image');
        }
        return $this ->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ).
        'chatsystem/admin/default.png';
    }

    /**
     * get Agent Data
     *
     * @return \Webkul\MagentoChatSystem\Model\AgentData
     */
    protected function getAgentData($agentId = null)
    {
        if (!$agentId) {
            $agentId = $this->getAgentId();
        }
        $this->helper->getAgentCurrentStatus($agentId);
        $agentModel = $this->agentDataFactory->create()->load($agentId, 'agent_id');
        return $agentModel;
    }

    /**
     * agent data by agent unique id
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Model\AgentData|bool
     */
    private function getAgentByUniqueId($uniqueId)
    {
        try {
            return $this->agentRepository->getByUniqueId($uniqueId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * super admin data
     *
     * @return array|null
     */
    protected function getSuperAdminData()
    {
        $adminUserCollection = $this->assignedChatFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $this->customerSession->getCustomerId()])
            ->addFieldToFilter('is_admin_chatting', ['eq' => 1])
            ->addFieldToSelect(['agent_id', 'agent_unique_id'])
            ->addFieldToFilter('chat_status', ['eq' => 1]);
        $superAdminData  = $adminUserCollection->getData();
        return $superAdminData;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isCustomerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isAdminLoggedIn()
    {
        $adminUserCollection = $this->agentDataFactory->create()
            ->getCollection()
            ->addFieldToFilter('agent_id', ['eq' => $this->getAgentId()]);
        return $adminUserCollection->getLastItem()->getChatStatus();
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
            ->addFieldToFilter('customer_id', ['eq' => $this->customerSession->getCustomerId()]);
        
        if ($adminUserCollection->getSize()) {
            return $adminUserCollection->getLastItem()->getAgentId();
        }
        return $id;
    }

    /**
     * agent unique id
     *
     * @return int|string
     */
    private function getAgentUniqueId()
    {
        $uniqueId = 0;
        $adminUserCollection = $this->assignedChatFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $this->customerSession->getCustomerId()]);
        if ($adminUserCollection->getSize()) {
            return $adminUserCollection->getLastItem()->getAgentUniqueId();
        }
        return $uniqueId;
    }
    /**
     * Retrieve customer registration URL
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->customerUrlManager->getRegisterUrl();
    }

    /**
     * check if node server is running
     *
     * @return bool
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
     * Retrieve store code
     *
     * @return string
     */
    private function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
}
