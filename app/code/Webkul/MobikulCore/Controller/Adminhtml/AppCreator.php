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

abstract class AppCreator extends \Magento\Backend\App\Action
{

    protected $filter;
    protected $storeManager;
    protected $coreRegistry;
    protected $mediaDirectory;
    protected $resultJsonFactory;
    protected $collectionFactory;
    protected $resultPageFactory;
    protected $carouselRepository;
    protected $fileUploaderFactory;
    protected $carouselDataFactory;
    protected $resultForwardFactory;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\MobikulCore\Api\CarouselRepositoryInterface $carouselRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Webkul\MobikulCore\Api\Data\CarouselInterfaceFactory $carouselDataFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Carousel\CollectionFactory $collectionFactory
    ) {
        $this->filter               = $filter;
        $this->coreRegistry         = $coreRegistry;
        $this->storeManager         = $storeManager;
        $this->mediaDirectory       = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->collectionFactory    = $collectionFactory;
        $this->resultPageFactory    = $resultPageFactory;
        $this->resultJsonFactory    = $resultJsonFactory;
        $this->carouselRepository   = $carouselRepository;
        $this->fileUploaderFactory  = $fileUploaderFactory;
        $this->carouselDataFactory  = $carouselDataFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::appcreator");
    }
}
