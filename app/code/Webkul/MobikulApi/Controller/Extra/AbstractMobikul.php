<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Extra;

use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Webkul\MobikulCore\Helper\Data as HelperData;

abstract class AbstractMobikul extends \Webkul\MobikulApi\Controller\ApiController
{
    protected $quote;
    protected $helper;
    protected $logger;
    protected $cmsPage;
    protected $toolbar;
    protected $baseDir;
    protected $emulate;
    protected $visitor;
    protected $eavConfig;
    protected $jsonHelper;
    protected $searchQuery;
    protected $deviceToken;
    protected $compareItem;
    protected $priceHelper;
    protected $coreSession;
    protected $imageHelper;
    protected $blockFactory;
    protected $storeManager;
    protected $cataloghelper;
    protected $catalogConfig;
    protected $pricingHelper;
    protected $productStatus;
    protected $filterProvider;
    protected $productFactory;
    protected $productCompare;
    protected $queryCollection;
    protected $customerSession;
    protected $filterAttribute;
    protected $categoryfactory;
    protected $customerFactory;
    protected $productVisibility;
    protected $productCollection;
    protected $compareCollection;
    protected $deviceTokenFactory;
    protected $mobikulNotification;
    protected $layerFilterAttribute;
    protected $filterableAttributes;
    protected $searchSuggestionHelper;
    protected $helperCatalog;

    public function __construct(
        Context $context,
        HelperData $helper,
        Emulation $emulate,
        \Magento\Cms\Model\Page $cmsPage,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Visitor $visitor,
        \Magento\Search\Model\Query $searchQuery,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Checkout\Helper\Data $cataloghelper,
        \Webkul\MobikulCore\Helper\Token $deviceToken,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Bundle\Model\Product\Price $bundlePriceModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Product\Compare $productCompare,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Layer\Filter\Attribute $filterAttribute,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Webkul\MobikulCore\Model\DeviceTokenFactory $deviceTokenFactory,
        \Webkul\MobikulCore\Model\NotificationFactory $mobikulNotification,
        \Webkul\MobikulCore\Helper\Searchsuggestion $searchSuggestionHelper,
        \Magento\Search\Model\ResourceModel\Query\Collection $queryCollection,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $compareItem,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute $layerFilterAttribute,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $compareCollection,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer
    ) {
        $this->quote = $quote;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->visitor = $visitor;
        $this->cmsPage = $cmsPage;
        $this->emulate = $emulate;
        $this->toolbar = $toolbar;
        $this->eavConfig = $eavConfig;
        $this->jsonHelper = $jsonHelper;
        $this->searchQuery = $searchQuery;
        $this->imageHelper = $imageHelper;
        $this->compareItem = $compareItem;
        $this->deviceToken = $deviceToken;
        $this->coreSession = $coreSession;
        $this->priceHelper = $priceHelper;
        $this->storeManager = $storeManager;
        $this->blockFactory = $blockFactory;
        $this->cataloghelper = $cataloghelper;
        $this->catalogConfig = $catalogConfig;
        $this->productStatus = $productStatus;
        $this->helperCatalog = $helperCatalog;
        $this->pricingHelper = $pricingHelper;
        $this->productCompare = $productCompare;
        $this->filterProvider = $filterProvider;
        $this->productFactory = $productFactory;
        $this->baseDir = $dir->getPath("media");
        $this->filterAttribute = $filterAttribute;
        $this->categoryFactory = $categoryFactory;
        $this->queryCollection = $queryCollection;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->bundlePriceModel = $bundlePriceModel;
        $this->compareCollection = $compareCollection;
        $this->productVisibility = $productVisibility;
        $this->productCollection = $productCollection;
        $this->deviceTokenFactory = $deviceTokenFactory;
        $this->mobikulNotification = $mobikulNotification;
        $this->layerFilterAttribute = $layerFilterAttribute;
        $this->filterableAttributes = $filterableAttributes;
        $this->searchSuggestionHelper = $searchSuggestionHelper;
        $this->fileDriver = $fileDriver;
        $this->serializer = $serializer;
        parent::__construct($helper, $context, $jsonHelper);
    }
}
