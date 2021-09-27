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
namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Message;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\CustomerFactory;
use Webkul\MagentoChatSystem\Model\CustomerData;
use Webkul\MagentoChatSystem\Model\MessageFactory;
use Magento\Framework\Filesystem\Io\File;

class LoadHistory extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var CustomerData
     */
    protected $customerData;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param CustomerFactory $customerFactory
     * @param CustomerData $customerData
     * @param MessageFactory $messageFactory
     * @param File $file
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Filesystem $fileSystem,
        CustomerFactory $customerFactory,
        CustomerData $customerData,
        MessageFactory $messageFactory,
        File $file
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->urlDecoder = $urlDecoder;
        $this->fileSystem = $fileSystem;
        $this->customerFactory = $customerFactory;
        $this->file = $file;
        $this->customerData = $customerData;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $data = $this->getRequest()->getParam('formData');
        if (isset($data['customerId'])) {
            $customerData = [];
            $customerData['messages'] = [];
            $customer = $this->customerFactory->create()->load($data['customerId']);

            $chatCustomerModel = $this->customerData
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $data['customerId']])
                ->addFieldToFilter('chat_status', ['neq' => 3])
                ->getFirstItem();

            $customerData['chatStatus'] = $chatCustomerModel->getChatStatus();
            $customerData['profileImageUrl'] = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).
            'chatsystem/profile/'
            .$data['customerId'].'/'.$chatCustomerModel->getImage();

            if ($chatCustomerModel->getImage() == '') {
                $customerData['profileImageUrl'] = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).
                'chatsystem/default.png';
            }
            $customerData['customer_name'] = $customer->getName();

            $customerUniqueId = $chatCustomerModel->getUniqueId();
            $messageModel = $this->messageFactory->create()
                ->getCollection()
                ->addFieldToFilter(
                    ['sender_unique_id', 'receiver_unique_id'],
                    [['eq' => $customerUniqueId], ['eq' => $customerUniqueId]]
                )
                ->addFieldToFilter(
                    ['sender_unique_id', 'receiver_unique_id'],
                    [['eq' => $data['agentUniqueId']],
                    ['eq' => $data['agentUniqueId']]]
                )
                ->setOrder('date', 'DESC')
                ->setPageSize(1);
            $lastMsgDate = $messageModel->getFirstItem()->getDate();

            $loadDate = date("Y-m-d H:i:s");
            $loadDate = date('Y-m-d H:i:s', strtotime($loadDate . ' -7 day'));

            $messageModel = $this->messageFactory->create()
                ->getCollection()
                ->addFieldToFilter(
                    ['sender_unique_id', 'receiver_unique_id'],
                    [['eq' => $customerUniqueId],
                    ['eq' => $customerUniqueId]]
                )
                ->addFieldToFilter(
                    ['sender_unique_id', 'receiver_unique_id'],
                    [['eq' => $data['agentUniqueId']],
                    ['eq' => $data['agentUniqueId']]]
                )
                ->addFieldToFilter('date', ['gteq'=> $loadDate])
                ->setOrder('date', 'ASC');

            $customerData['messages'] = [];
            foreach ($messageModel as $key => $value) {
                $data = $value->getData();
                $data['time'] = date('h:i A', strtotime($data['date']));
                $data['date'] = date('Y-m-d', strtotime($data['date']));
                $data['type'] = 'text';

                $file = $this->urlDecoder->decode($data['message']);
                $directory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);

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
                    };
                }
                $customerData['messages'][$key] = $data;
            }
            $response->setMessageData($customerData);
            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        }
    }
}
