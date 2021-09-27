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
 * Featuredcategories Class controller
 */
abstract class Featuredcategories extends \Magento\Backend\App\Action
{
    protected $date;
    protected $filter;
    protected $jsonHelper;
    protected $storeManager;
    protected $mediaDirectory;
    protected $resultPageFactory;
    protected $collectionFactory;
    protected $resultJsonFactory;
    protected $categoryRepository;
    protected $coreRegistry = null;
    protected $fileUploaderFactory;
    protected $resultForwardFactory;
    protected $categoryResourceModel;
    protected $featuredcategoriesRepository;
    protected $featuredcategoriesDataFactory;

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Webkul\MobikulCore\Api\FeaturedcategoriesRepositoryInterface $featuredcategoriesRepository,
        \Webkul\MobikulCore\Api\Data\FeaturedcategoriesInterfaceFactory $featuredcategoriesDataFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Featuredcategories\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Downloadable\Helper\File $fileHelper
    ) {
        parent::__construct($context);
        $this->date = $date;
        $this->filter = $filter;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->formKeyValidator = $formKeyValidator;
        $this->collectionFactory = $collectionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->categoryRepository = $categoryRepository;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->featuredcategoriesRepository = $featuredcategoriesRepository;
        $this->featuredcategoriesDataFactory = $featuredcategoriesDataFactory;
        $this->fileDriver = $fileDriver;
        $this->fileHelper = $fileHelper;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::featuredcategories");
    }
}
