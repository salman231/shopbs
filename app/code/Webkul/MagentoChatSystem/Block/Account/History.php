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
namespace Webkul\MagentoChatSystem\Block\Account;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Theme\Block\Html\Pager;
use Magento\Framework\Filesystem\Io\File;

class History extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Webkul\MagentoChatSystem\Model\ChatDataConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var ChatHistoryCollection
     */
    protected $history;

    /**
     * @var \Webkul\MagentoChatSystem\Model\CustomerDataFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Webkul\MagentoChatSystem\Model\MessageFactory
     */
    protected $messageFactory;

    /**
     * @var \Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\Collection
     */
    protected $agentDataCollection;

    /**
     * @var File
     */
    protected $file;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Webkul\MagentoChatSystem\Model\CustomerDataFactory $customerDataFactory
     * @param \Webkul\MagentoChatSystem\Model\MessageFactory $messageFactory
     * @param \Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\Collection $agentDataCollection
     * @param File $file
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Webkul\MagentoChatSystem\Model\CustomerDataFactory $customerDataFactory,
        \Webkul\MagentoChatSystem\Model\MessageFactory $messageFactory,
        \Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\Collection $agentDataCollection,
        File $file,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerSessionFactory = $customerSessionFactory;
        $this->urlDecoder = $urlDecoder;
        $this->customerDataFactory = $customerDataFactory;
        $this->messageFactory = $messageFactory;
        $this->agentDataCollection = $agentDataCollection;
        $this->file = $file;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Chat History'));
    }

    /**
     * Message collection
     *
     * @return \Webkul\MagentoChatSystem\Model\ResourceModel\Message\Collection
     */
    public function getHistoryCollection()
    {
        if (!$this->history) {
            $paramData = $this->getRequest()->getParams();
            $agentName = '';
            $msgDate = '';
            if (isset($paramData['agent'])) {
                $agentName = $paramData['agent'];
            }
            if (isset($paramData['msg_date'])) {
                $msgDate = $paramData['msg_date'];
            }

            $customerId = $this->customerSessionFactory->create()->getCustomerId();
            $agentDataTable = $this->agentDataCollection->getTable('chatsystem_agentdata');

            $customerData = $this->customerDataFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);

            $customerUniqueId = $customerData->getLastItem()->getUniqueId();

            $chatHistoryCollection = $this->messageFactory->create()->getCollection()
            ->addFieldToFilter(
                ['sender_unique_id', 'receiver_unique_id'],
                [['eq' => $customerUniqueId], ['eq' => $customerUniqueId]]
            )->setOrder('date', 'DESC');

            if ($msgDate) {
                $date = date_create($msgDate);
                $msgDate = date_format($date, 'Y-m-d H:i:s');
                $next_msgDate = date_modify($date, '+1 day');
                $next_msgDate = date_format($next_msgDate, 'Y-m-d H:i:s');
                $chatHistoryCollection->addFieldToFilter('date', ['lteq'=> $next_msgDate])
                            ->addFieldToFilter('date', ['gteq'=> $msgDate]);
            }

            $chatHistoryCollection->getSelect()->join(
                $agentDataTable.' as adt',
                'main_table.sender_unique_id = adt.agent_unique_id OR 
                main_table.receiver_unique_id = adt.agent_unique_id',
                ['agent_name' => 'agent_name']
            );

            if ($agentName !== '') {
                $chatHistoryCollection->getSelect()->where(
                    'adt.agent_name like "%'.$agentName.'%"'
                );
            }
            $this->history = $chatHistoryCollection;
        }
        return $this->history;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getHistoryCollection()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'chat.history.list.pager'
            )->setCollection(
                $this->getHistoryCollection()
            );
            $this->setChild('pager', $pager);
            $this->getHistoryCollection()->load();
        }

        return $this;
    }

    /**
     * Get Pager Html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Customer Unique Id
     *
     * @return int
     */
    public function getCustomerUniqueId()
    {
        $customerId = $this->customerSessionFactory->create()->getCustomerId();
        $customerData = $this->customerDataFactory->create()->getCollection()
        ->addFieldToFilter('customer_id', ['eq' => $customerId]);

        return $customerData->getLastItem()->getUniqueId();
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'chatsystem/chat_options/'.$field;
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * return message type
     *
     * @param string $message
     * @return string
     */
    public function getMessageType($message)
    {
        $type = 'text';
        $file = $this->urlDecoder->decode($message);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);

        $fileName = 'chatsystem/attachments/'.ltrim($file, '/');

        $filePath = $directory->getAbsolutePath($fileName);
        
        if ($directory->isFile($fileName)) {
            $info = $this->file->getPathInfo($filePath);
            $extension = $info['extension'];
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    $type = 'image';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    $type = 'image';
                    break;
                case 'jpeg':
                    $contentType = 'image/jpeg';
                    $type = 'image';
                    break;
                case 'PNG':
                    $contentType = 'image/png';
                    $type = 'image';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    $type = 'image';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    $type = 'file';
                    break;
            }
        }
        return $type;
    }
}
