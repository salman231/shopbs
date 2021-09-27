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
namespace Webkul\MagentoChatSystem\Controller\Profile;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

class Upload extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var Webkul\MagentoChatSystem\Model\CustomerDataFactory
     */
    protected $customerDataFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param CustomerSessionFactory $customerSessionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        CustomerSessionFactory $customerSessionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MagentoChatSystem\Model\CustomerDataFactory $customerDataFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customerDataFactory = $customerDataFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'chatsystem/profile/'.$this->customerSessionFactory->create()->getCustomerId()
        );
        $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).
                'chatsystem/profile/'.$this->customerSessionFactory->create()->getCustomerId();
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'file']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($path);
            $collection = $this->customerDataFactory->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', ['eq' => $this->customerSessionFactory->create()->getCustomerId()]);
            if ($collection->getSize()) {
                $entityId = $collection->getLastItem()->getEntityId();
                $model = $this->customerDataFactory->create()->load($entityId);
                $model->setImage($result['file']);
                $model->setId($entityId);
                $model->save();
            }
            $response->setImageName($url.'/'.$result['file']);
            $response->setMessage(
                __('Image updated successfully.')
            );
        } catch (\Exception $e) {
            $response->setMessage(
                $e->getMessage()
            );
            $response->setError(true);
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
