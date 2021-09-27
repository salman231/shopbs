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
 * Categoryimages Class controller
 */
abstract class Categoryimages extends \Magento\Backend\App\Action
{
    protected $date;
    protected $file;
    protected $filter;
    protected $jsonHelper;
    protected $storeManager;
    protected $mediaDirectory;
    protected $resultJsonFactory;
    protected $collectionFactory;
    protected $resultPageFactory;
    protected $categoryRepository;
    protected $coreRegistry = null;
    protected $fileUploaderFactory;
    protected $resultForwardFactory;
    protected $categoryResourceModel;
    protected $categoryimagesRepository;
    protected $categoryimagesDataFactory;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Webkul\MobikulCore\Api\CategoryimagesRepositoryInterface $categoryimagesRepository,
        \Webkul\MobikulCore\Api\Data\CategoryimagesInterfaceFactory $categoryimagesDataFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Categoryimages\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Downloadable\Helper\File $fileHelper
    ) {
        parent::__construct($context);
        $this->date = $date;
        $this->file = $file;
        $this->filter = $filter;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->categoryRepository = $categoryRepository;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->categoryimagesRepository = $categoryimagesRepository;
        $this->categoryimagesDataFactory = $categoryimagesDataFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileDriver = $fileDriver;
        $this->fileHelper = $fileHelper;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::categoryimages");
    }
}
