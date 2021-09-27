<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;

abstract class Deliveryboy extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface
     */
    protected $deliveryboyRepository;

    /**
     * @var \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterfaceFactory
     */
    protected $deliveryboyDataFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Operation
     */
    protected $operationHelper;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $deliveryboyHelper;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryboyRepository
     * @param \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterfaceFactory $deliveryboyDataFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Webkul\DeliveryBoy\Helper\Operation $operationHelper
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryboyRepository,
        \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterfaceFactory $deliveryboyDataFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $collectionFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Webkul\DeliveryBoy\Helper\Operation $operationHelper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
    ) {
        parent::__construct($context);
        
        $this->date = $date;
        $this->filter = $filter;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->operationHelper = $operationHelper;
        $this->deliveryboyRepository = $deliveryboyRepository;
        $this->deliveryboyDataFactory = $deliveryboyDataFactory;
        try {
            $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $this->mediaDirectory = null;
        }
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
        $this->deliveryboyHelper = $deliveryboyHelper;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_DeliveryBoy::deliveryboy");
    }
}
