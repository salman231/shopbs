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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Categoryimages;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Block class Banner to upload multiple banner for category Carousel
 */
class Banner extends \Magento\Backend\Block\Widget\Container
{
    protected $selectedIds = [];
    protected $expandedPath = [];

    /**
     * Construct function for class Banner
     *
     * @param \Magento\Framework\Registry                               $registry                 registry
     * @param \Magento\Backend\Block\Widget\Context                     $context                  context
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager             storeManager
     * @param \Webkul\MobikulCore\Api\CategoryimagesRepositoryInterface $categoryimagesRepository
     * categoryimagesRepository
     * @param array                                                     $data                     data  = []
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MobikulCore\Api\CategoryimagesRepositoryInterface $categoryimagesRepository,
        \Webkul\MobikulCore\Helper\Data $coreHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->categoryimagesRepository = $categoryimagesRepository;
        $this->coreHelper = $coreHelper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }
 
    /**
     * Function prepare layout to set template for the block
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate("Webkul_MobikulCore::categoryimages/banner.phtml");
    }

    /**
     * Function to get Category Banner Data
     *
     * @return array
     */
    public function getCategoryBannerData()
    {
        $categoryImageId = $this->getRequest()->getParam("id");
        $categoryData = $this->categoryimagesRepository->getById($categoryImageId);
        return $categoryData;
    }

    /**
     * Function to get category Id
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->registry->registry("categoryId") ? : null;
    }
}
