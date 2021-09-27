<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Notification Class controller
 */
abstract class Notification extends \Magento\Backend\App\Action
{
    protected $date;
    protected $filter;
    protected $helper;
    protected $jsonHelper;
    protected $entityType;
    protected $productType;
    protected $storeManager;
    protected $attributeSet;
    protected $mediaDirectory;
    protected $entityAttribute;
    protected $productResource;
    protected $categoryResource;
    protected $resultJsonFactory;
    protected $resultPageFactory;
    protected $collectionFactory;
    protected $coreRegistry = null;
    protected $fileUploaderFactory;
    protected $resultForwardFactory;
    protected $notificationRepository;
    protected $notificationDataFactory;
    protected $productRepositoryInterface;
    protected $categoryRepositoryInterface;

    public function __construct(
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Flat $entityType,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attributeSet,
        \Webkul\MobikulCore\Api\NotificationRepositoryInterface $notificationRepository,
        \Webkul\MobikulCore\Api\Data\NotificationInterfaceFactory $notificationDataFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Notification\CollectionFactory $collectionFactory,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Downloadable\Helper\File $fileHelper,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        parent::__construct($context);
        $this->date = $date;
        $this->filter = $filter;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->entityType = $entityType;
        $this->productType = $productType;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->attributeSet = $attributeSet;
        $this->entityAttribute = $entityAttribute;
        $this->productResource = $productResource;
        $this->categoryResource = $categoryResource;
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->notificationRepository = $notificationRepository;
        $this->notificationDataFactory = $notificationDataFactory;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->serializer = $serializer;
        $this->fileDriver = $fileDriver;
        $this->fileHelper = $fileHelper;
        $this->curl = $curl;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::notification");
    }
}
