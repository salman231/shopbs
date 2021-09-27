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
 * Carouselimage Class controller
 */
abstract class Carouselimage extends \Magento\Backend\App\Action
{
    protected $filter;
    protected $storeManager;
    protected $coreRegistry;
    protected $mediaDirectory;
    protected $resultJsonFactory;
    protected $collectionFactory;
    protected $resultPageFactory;
    protected $fileUploaderFactory;
    protected $resultForwardFactory;
    protected $carouselimageRepository;
    protected $carouselimageDataFactory;
    protected $productRepositoryInterface;
    protected $categoryRepositoryInterface;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
        \Webkul\MobikulCore\Api\CarouselimageRepositoryInterface $carouselimageRepository,
        \Webkul\MobikulCore\Api\Data\CarouselimageInterfaceFactory $carouselimageDataFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Carouselimage\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Downloadable\Helper\File $fileHelper
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->carouselimageRepository = $carouselimageRepository;
        $this->carouselimageDataFactory = $carouselimageDataFactory;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileDriver = $fileDriver;
        $this->fileHelper = $fileHelper;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::carouselimage");
    }
}
