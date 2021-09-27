<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Catalog;

abstract class AbstractCatalog extends \Webkul\MobikulApi\Controller\ApiController
{
    protected $dir;
    protected $date;
    protected $vote;
    protected $items;
    protected $store;
    protected $helper;
    protected $rating;
    protected $review;
    protected $emulate;
    protected $escaper;
    protected $toolbar;
    protected $compare;
    protected $baseDir;
    protected $headers;
    protected $cmsPage;
    protected $wishlist;
    protected $category;
    protected $taxHelper;
    protected $eavConfig;
    protected $sendFriend;
    protected $localeDate;
    protected $jsonHelper;
    protected $quoteModel;
    protected $connection;
    protected $bannerImage;
    protected $linkFactory;
    protected $stockFilter;
    protected $imageHelper;
    protected $searchLayer;
    protected $coreRegistry;
    protected $localeFormat;
    protected $queryFactory;
    protected $eventManager;
    protected $productPrice;
    protected $mobikulLayer;
    protected $categoryTree;
    protected $productLinks;
    protected $catalogLayer;
    protected $priceCurrency;
    protected $categoryLayer;
    protected $productOption;
    protected $helperCatalog;
    protected $pricingHelper;
    protected $catalogConfig;
    protected $cmsCollection;
    protected $productStatus;
    protected $customerImage;
    protected $productSample;
    protected $stockRegistry;
    protected $categoryHelper;
    protected $groupedProduct;
    protected $checkoutHelper;
    protected $layerAttribute;
    protected $downloadSample;
    protected $websiteManager;
    protected $downloadHelper;
    protected $storeInterface;
    protected $productFactory;
    protected $filterProvider;
    protected $carouselFactory;
    protected $customerFactory;
    protected $customerVisitor;
    protected $customerSession;
    protected $bundlePriceModel;
    protected $searchCollection;
    protected $compareListBlock;
    protected $configurableBlock;
    protected $membershipBlock;
    protected $productCollection;
    protected $appcreatorFactory;
    protected $currencyInterface;
    protected $productRepository;
    protected $productVisibility;
    protected $mobikulLayerPrice;
    protected $productCountSelect;
    protected $productOptionBlock;
    protected $featuredCategories;
    protected $compareItemFactory;
    protected $mobikulNotification;
    protected $catalogHelperOutput;
    protected $filterableAttributes;
    protected $categoryImageFactory;
    protected $productResourceModel;
    protected $carouselImageFactory;
    protected $advancedCatalogSearch;
    protected $categoryResourceModel;
    protected $reportCollectionFactory;
    protected $filterPriceDataprovider;
    protected $categoryCollectionFactory;
    protected $productResourceCollection;
    protected $layerFilterAttributeResource;
    protected $compareItemCollectionFactory;
    protected $_MembershipProductsFactory;
    protected $_ruleCollectionFactory;
    protected $_customerGroup;
    protected $_catalogRule;
    protected $moduleManager;
    public function __construct(
        \Magento\Cms\Model\Page $cmsPage,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\Escaper $escaper,
        \Magento\Review\Model\Rating $rating,
        \Magento\Review\Model\Review $review,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Quote\Model\Quote $quoteModel,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Catalog\Helper\Data $taxHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Webkul\MobikulCore\Model\Layer $mobikulLayer,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Locale\Format $localeFormat,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Review\Model\Rating\Option\Vote $vote,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\Layer\Search $searchLayer,
        \Magento\SendFriend\Model\SendFriend $sendFriend,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\CategoryFactory $category,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Helper\Product\Compare $compare,
        \Magento\Wishlist\Model\WishlistFactory $wishlist,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Downloadable\Helper\File $downloadHelper,
        \Webkul\MobikulCore\Model\Bannerimage $bannerImage,
        \Webkul\MobikulCore\Model\UserImage $customerImage,
        \Magento\Downloadable\Model\Sample $downloadSample,
        \Magento\Catalog\Block\Product\Price $productPrice,
        \Magento\Store\Model\WebsiteFactory $websiteManager,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Catalog\Helper\Output $catalogHelperOutput,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Product\Option $productOption,
        \Magento\Downloadable\Model\LinkFactory $linkFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Bundle\Model\Product\Price $bundlePriceModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\ResourceConnection $connection,
        \Webkul\MobikulCore\Model\Category\Tree $categoryTree,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\MobikulApi\Block\Configurable $configurableBlock,
        \Webkul\MobikulApi\Block\Membership $membershipBlock,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Webkul\MobikulCore\Model\CarouselFactory $carouselFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeInterface,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar,
        \Magento\Catalog\Model\Layer\Filter\Category $categoryLayer,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\CatalogSearch\Model\Advanced $advancedCatalogSearch,
        \Magento\Catalog\Model\Layer\Filter\Attribute $filterAttribute,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface,
        \Webkul\MobikulCore\Model\AppcreatorFactory $appcreatorFactory,
        \Magento\Downloadable\Block\Catalog\Product\Links $productLinks,
        \Magento\Cms\Model\ResourceModel\Page\Collection $cmsCollection,
        \Magento\Catalog\Block\Product\View\Options $productOptionBlock,
        \Webkul\MobikulCore\Model\Featuredcategories $featuredCategories,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Webkul\MobikulCore\Model\NotificationFactory $mobikulNotification,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Downloadable\Block\Catalog\Product\Samples $productSample,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Webkul\MobikulCore\Model\CarouselimageFactory $carouselImageFactory,
        \Magento\Catalog\Block\Product\Compare\ListCompare $compareListBlock,
        \Magento\Catalog\Model\Layer\Filter\AttributeFactory $layerAttribute,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Webkul\MobikulCore\Model\CategoryimagesFactory $categoryImageFactory,
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Webkul\MobikulCore\Model\ResourceModel\Layer\Filter\Price $mobikulLayerPrice,
        \Magento\CatalogSearch\Model\ResourceModel\Search\Collection $searchCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productResourceCollection,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes,
        \Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory $filterPriceDataprovider,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $reportCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $layerFilterAttributeResource,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $compareItemCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection $bestSellerCollection,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\CatalogWidget\Block\Product\ProductsList $productList,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64Json,
        \Magento\Catalog\Model\Product\Compare\ListCompare $listCompareModel,
        \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory,
        \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\CatalogRule\Model\Rule $catalogRule
    ) {
        $this->date = $date;
        $this->vote = $vote;
        $this->store = $store;
        $this->helper = $helper;
        $this->rating = $rating;
        $this->review = $review;
        $this->escaper = $escaper;
        $this->compare = $compare;
        $this->baseDir = $dir->getPath("media");
        $this->toolbar = $toolbar;
        $this->cmsPage = $cmsPage;
        $this->emulate = $emulate;
        $this->category = $category;
        $this->wishlist = $wishlist;
        $this->taxHelper = $taxHelper;
        $this->eavConfig = $eavConfig;
        $this->connection = $connection;
        $this->sendFriend = $sendFriend;
        $this->localeDate = $localeDate;
        $this->quoteModel = $quoteModel;
        $this->jsonHelper = $jsonHelper;
        $this->searchLayer = $searchLayer;
        $this->linkFactory = $linkFactory;
        $this->bannerImage = $bannerImage;
        $this->stockFilter = $stockFilter;
        $this->imageHelper = $imageHelper;
        $this->catalogLayer = $layerResolver->get();
        $this->categoryTree = $categoryTree;
        $this->localeFormat = $localeFormat;
        $this->eventManager = $eventManager;
        $this->queryFactory = $queryFactory;
        $this->mobikulLayer = $mobikulLayer;
        $this->coreRegistry = $coreRegistry;
        $this->productLinks = $productLinks;
        $this->productPrice = $productPrice;
        $this->priceCurrency = $priceCurrency;
        $this->productSample = $productSample;
        $this->helperCatalog = $helperCatalog;
        $this->customerImage = $customerImage;
        $this->productStatus = $productStatus;
        $this->categoryLayer = $categoryLayer;
        $this->pricingHelper = $pricingHelper;
        $this->catalogConfig = $catalogConfig;
        $this->cmsCollection = $cmsCollection;
        $this->productOption = $productOption;
        $this->layerResolver = $layerResolver;
        $this->stockRegistry = $stockRegistry;
        $this->categoryHelper = $categoryHelper;
        $this->groupedProduct = $groupedProduct;
        $this->downloadHelper = $downloadHelper;
        $this->layerAttribute = $layerAttribute;
        $this->downloadSample = $downloadSample;
        $this->storeInterface = $storeInterface;
        $this->websiteManager = $websiteManager;
        $this->checkoutHelper = $checkoutHelper;
        $this->productFactory = $productFactory;
        $this->localeResolver = $localeResolver;
        $this->filterProvider = $filterProvider;
        $this->customerSession = $customerSession;
        $this->filterAttribute = $filterAttribute;
        $this->carouselFactory = $carouselFactory;
        $this->customerVisitor = $customerVisitor;
        $this->currencyFactory = $currencyFactory;
        $this->customerFactory = $customerFactory;
        $this->bundlePriceModel = $bundlePriceModel;
        $this->compareListBlock = $compareListBlock;
        $this->searchCollection = $searchCollection;
        $this->appcreatorFactory = $appcreatorFactory;
        $this->currencyInterface = $currencyInterface;
        $this->configurableBlock = $configurableBlock;
        $this->membershipBlock = $membershipBlock;
        $this->mobikulLayerPrice = $mobikulLayerPrice;
        $this->productRepository = $productRepository;
        $this->productVisibility = $productVisibility;
        $this->productCollection = $productCollection;
        $this->featuredCategories = $featuredCategories;
        $this->compareItemFactory = $compareItemFactory;
        $this->productOptionBlock = $productOptionBlock;
        $this->catalogHelperOutput = $catalogHelperOutput;
        $this->mobikulNotification = $mobikulNotification;
        $this->categoryImageFactory = $categoryImageFactory;
        $this->carouselImageFactory = $carouselImageFactory;
        $this->filterableAttributes = $filterableAttributes;
        $this->productResourceModel = $productResourceModel;
        $this->advancedCatalogSearch = $advancedCatalogSearch;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->reportCollectionFactory = $reportCollectionFactory;
        $this->filterPriceDataprovider = $filterPriceDataprovider;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productResourceCollection = $productResourceCollection;
        $this->layerFilterAttributeResource = $layerFilterAttributeResource;
        $this->compareItemCollectionFactory = $compareItemCollectionFactory;
        $this->bestSellerCollection = $bestSellerCollection;
        $this->fileDriver = $fileDriver;
        $this->productList = $productList;
        $this->serializer = $serializer;
        $this->base64Json = $base64Json;
        $this->listCompareModel = $listCompareModel;
        $this->_MembershipProductsFactory = $MembershipProductsFactory;
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
        $this->_customerGroup = $customerGroup;
        $this->_catalogRule = $catalogRule;
        $this->moduleManager = $moduleManager;
        parent::__construct($helper, $context, $jsonHelper);
    }

    /**
     * Function to Filter Product Collection
     *
     * @return void
     */
    protected function filterProductCollection()
    {
        if (count($this->_filterData) > 0) {
            $filterCount = count($this->_filterData[0]);
            for ($i=0; $i<$filterCount; ++$i) {
                if ($this->_filterData[0][$i] != "" && $this->_filterData[1][$i] == "price") {
                    $priceRange = explode("-", $this->_filterData[0][$i]);
                    $to = $priceRange[1];
                    $from = $priceRange[0];
                    $currencyRate = $this->_collection->getCurrencyRate();
                    $fromRange = ($from - (.01 / 2)) / $currencyRate;
                    $toRange = ($to - (.01 / 2)) / $currencyRate;
                    $select = $this->_collection->getSelect();
                    $isFlatEnabled = $this->_productResourceCollection->isEnabledFlat();
                    if ($isFlatEnabled) {
                        if ($from !== "") {
                            $select->where("price_index.price".">=".$fromRange);
                        }
                        if ($to !== "") {
                            $select->where("price_index.price"."<".$toRange);
                        }
                    } else {
                        if ($from !== "") {
                            $select->where("price_index.min_price".">=".$fromRange);
                        }
                        if ($to !== "") {
                            $select->where("price_index.min_price"."<".$toRange);
                        }
                    }
                } elseif ($this->_filterData[0][$i] != "" && $this->_filterData[1][$i] == "cat") {
                    $categoryToFilter = $this->_category->create()->load($this->_filterData[0][$i]);
                    $this->_collection->setStoreId($this->_storeId)->addCategoryFilter($categoryToFilter);
                } else {
                    $attribute = $this->_eavConfig->getAttribute("catalog_product", $this->_filterData[1][$i]);
                    $attributeModel = $this->_layerAttribute->create()->setAttributeModel($attribute);
                    $filterAtr = $this->_layerFilterAttributeResource->create();
                    $connection = $filterAtr->getConnection();
                    $tableAlias = $attribute->getAttributeCode()."_idx";
                    $conditions = [
                        "{$tableAlias}.entity_id = e.entity_id",
                        $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                        $connection->quoteInto("{$tableAlias}.store_id = ?", $this->_collection->getStoreId()),
                        $connection->quoteInto("{$tableAlias}.value = ?", $this->_filterData[0][$i]),
                    ];
                    $this->_collection->getSelect()->join(
                        [$tableAlias=>$filterAtr->getMainTable()],
                        implode(" AND ", $conditions),
                        []
                    );
                }
            }
        }
    }

    /**
     * Function to sort product Collection
     *
     * @return void
     */
    protected function sortProductCollection()
    {
        if (count($this->_sortData) > 0) {
            $sortBy = $this->_sortData[0];
            if ($this->_sortData[1] == 0) {
                $this->_collection->setOrder($sortBy, "ASC");
            } else {
                $this->_collection->setOrder($sortBy, "DESC");
            }
        } else {
            $this->_collection->setOrder("position", "ASC");
        }
    }

    /**
     * Function to get Sorting Data
     *
     * @return void
     */
    protected function getSortingData()
    {
        $sortingData = [];
        $toolbar = $this->toolbar;
        foreach ($toolbar->getAvailableOrders() as $key => $order) {
            $each = [];
            $each["code"] = $key;
            $each["label"] = __($order);
            $sortingData[] = $each;
        }
        $this->_returnArray["sortingData"] = $sortingData;
    }

    /**
     * Function get layered Data
     *
     * @return void
     */
    protected function getLayeredData()
    {
        $this->_mobikulLayer->_customCollection = $this->_collection;
        $this->_mobikulLayerPrice->_customCollection = $this->_collection;
        $layeredData = [];
        $doPrice = true;
        if (count($this->_filterData) > 0) {
            if (in_array("price", $this->_filterData[1])) {
                $doPrice = false;
            }
        }
        $filters = $this->_filterableAttributes->getList();
        foreach ($filters as $filter) {
            if ($filter->getFrontendInput() == "price") {
                if ($doPrice) {
                    $priceFilterModel = $this->_filterPriceDataprovider->create();
                    if ($priceFilterModel) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getStoreLabel();
                        $each["options"] = $this->_helperCatalog->getPriceFilter($priceFilterModel, $this->_storeId);
                        if (!empty($each["options"])) {
                            $layeredData[] = $each;
                        }
                    }
                }
            } else {
                $doAttribute = true;
                if (count($this->_filterData) > 0) {
                    if (in_array($filter->getAttributeCode(), $this->_filterData[1])) {
                        $doAttribute = false;
                    }
                }
                if ($doAttribute) {
                    $attributeFilterModel = $this->_layerAttribute->create()->setAttributeModel($filter);
                    if ($attributeFilterModel->getItemsCount()) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getStoreLabel();
                        $each["options"] = $this->_helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                        if (!empty($each["options"])) {
                            $layeredData[] = $each;
                        }
                    }
                }
            }
        }
        $this->_returnArray["layeredData"] = $layeredData;
    }
}
