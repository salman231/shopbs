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

namespace Webkul\MobikulCore\Block\Adminhtml\AppCreator;

/**
 * Class AppCreator block
 */
class AppCreator extends \Magento\Backend\Block\Template
{
    const MAX_LAYOUT_NO = '20';
    const ENABLED = true;
    protected $coreRegistry;
    protected $appcreatorFactory;
    protected $date;
    protected $store;
    protected $helper;
    protected $baseDir;
    protected $category;
    protected $localeDate;
    protected $bannerImage;
    protected $stockFilter;
    protected $helperCatalog;
    protected $productStatus;
    protected $catalogConfig;
    protected $storeInterface;
    protected $carouselFactory;
    protected $productVisibility;
    protected $productCollection;
    protected $featuredCategories;
    protected $carouselImageFactory;
    protected $productResourceModel;
    protected $categoryResourceModel;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\MobikulCore\Model\AppcreatorFactory $appcreatorFactory,
        \Magento\Store\Model\Store $store,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\CategoryFactory $category,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Webkul\MobikulCore\Model\Bannerimage $bannerImage,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Webkul\MobikulCore\Model\CarouselFactory $carouselFactory,
        \Magento\Store\Model\StoreManagerInterface $storeInterface,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Webkul\MobikulCore\Model\Featuredcategories $featuredCategories,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel,
        \Webkul\MobikulCore\Model\CarouselimageFactory $carouselImageFactory,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->_appcreatorFactory = $appcreatorFactory;
        $this->date = $date;
        $this->store = $store;
        $this->helper = $helper;
        $this->baseDir = $dir->getPath("media");
        $this->category = $category;
        $this->localeDate = $localeDate;
        $this->stockFilter = $stockFilter;
        $this->helperCatalog = $helperCatalog;
        $this->productStatus = $productStatus;
        $this->catalogConfig = $catalogConfig;
        $this->bannerImage = $bannerImage;
        $this->storeInterface = $storeInterface;
        $this->carouselFactory = $carouselFactory;
        $this->productVisibility = $productVisibility;
        $this->productCollection = $productCollection;
        $this->fileDriver = $fileDriver;
        $this->featuredCategories = $featuredCategories;
        $this->carouselImageFactory = $carouselImageFactory;
        $this->productResourceModel = $productResourceModel;
        $this->categoryResourceModel = $categoryResourceModel;
        parent::__construct($context, $data);
        $this->bannerWidth= 500;
        $this->height = 500;
        $this->iconWidth = 10;
        $this->iconHeight = 10;
        $this->storeId = 1;
        $this->width  = 100;
        $this->getFeaturedCategories();
        $this->getFeaturedDeals();
        $this->getBannerImages();
        $this->getHotDeals();
        $this->getNewDeals();
        $this->getImageNProductCarousel();
    }

   /**
    * Function to get Image and Product Carousel
    * Set carousels to return array
    *
    * @return none
    */
    public function getImageNProductCarousel()
    {
        $collection = $this->carouselFactory->create()->getCollection()
            ->addFieldToFilter("status", 1)
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => 1]
            ])
            ->setOrder("sort_order", "ASC");
        foreach ($collection as $eachCarousel) {
            if ($eachCarousel->getType() == 2) {
                $oneCarousel = [];
                $productList = [];
                $oneCarousel["id"] = $eachCarousel->getId();
                $oneCarousel["type"] = "product";
                $oneCarousel["label"] = $eachCarousel->getTitle();
                if ($eachCarousel->getColorCode()) {
                    $oneCarousel["color"] = $eachCarousel->getColorCode();
                }
                if ($eachCarousel->getFilename()) {
                    $filePath = $this->helper->getUrl("media")."mobikul/carousel/".$eachCarousel->getFilename();
                    $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikul/carousel/".$eachCarousel
                        ->getFilename();
                    $oneCarousel["image"] = $filePath;
                    $oneCarousel["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                }
                // $oneCarousel["order"] = $eachCarousel->getSortOrder();
                $selectedProdctIds = explode(",", $eachCarousel->getProductIds());
                $productCollection = $this->productCollection->create()
                    ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                    ->addAttributeToSelect("image")
                    ->addAttributeToSelect("thumbnail")
                    ->addAttributeToSelect("small_image")
                    ->addAttributeToFilter("entity_id", ["in"=>$selectedProdctIds])
                    ->setVisibility($this->productVisibility->getVisibleInSiteIds())
                    ->addStoreFilter();
                if ($this->helperCatalog->showOutOfStock() == 0) {
                    $this->stockFilter->addInStockFilterToCollection($productCollection);
                }
                $productCollection->setPageSize(5)->setCurPage(1);
                foreach ($productCollection as $eachProduct) {
                    $productList[] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $this->storeId,
                        $this->width
                    );
                }
                $oneCarousel["productList"] = $productList;
                if (count($oneCarousel["productList"])) {
                    $this->returnArray["carousel"][] = $oneCarousel;
                }
            } else {
                $banners = [];
                $oneCarousel = [];
                $oneCarousel["id"] = $eachCarousel->getId();
                $oneCarousel["type"] = "image";
                $oneCarousel["label"] = $eachCarousel->getTitle();
                if ($eachCarousel->getColorCode()) {
                    $oneCarousel["color"] = $eachCarousel->getColorCode();
                }
                if ($eachCarousel->getFilename()) {
                    $filePath = $this->helper->getUrl("media")."mobikul/carousel/".$eachCarousel->getFilename();
                    $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikul/carousel/".$eachCarousel
                        ->getFilename();
                    $oneCarousel["image"] = $filePath;
                    $oneCarousel["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                }
                // $oneCarousel["order"] = $eachCarousel->getSortOrder();
                $sellectedBanners = explode(",", $eachCarousel->getImageIds());
                $carouselImageColelction = $this->carouselImageFactory->create()->getCollection()
                    ->addFieldToFilter("id", ["in"=>$sellectedBanners]);
                foreach ($carouselImageColelction as $each) {
                    $oneBanner = [];
                    $newUrl = "";
                    $dominantColorPath = "";
                    $basePath = $this->baseDir.'/'.$each->getFilename();
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir.'/'."mobikulresized".'/'.$this->bannerWidth."x".
                            $this->height.'/'.$each->getFilename();
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->height);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".'/'.
                            $this->bannerWidth."x".$this->height.'/'.$each->getFilename();
                        $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".'/'.
                            $this->bannerWidth."x".$this->height.'/'.$each->getFilename();
                    }
                    $oneBanner["url"] = $newUrl;
                    $oneBanner["title"] = $each->getTitle();
                    $oneBanner["bannerType"] = $each->getType();
                    $oneBanner["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                    if ($each->getType() == "category") {
                        $categoryName = $this->categoryResourceModel->getAttributeRawValue(
                            $each->getProCatId(),
                            "name",
                            $this->storeId
                        );
                        if (is_array($categoryName)) {
                            continue;
                        }
                        $oneBanner["id"] = $each->getProCatId();
                        $oneBanner["name"] = $categoryName;
                    } elseif ($each->getType() == "product") {
                        $productName = $this->productResourceModel->getAttributeRawValue(
                            $each->getProCatId(),
                            "name",
                            $this->storeId
                        );
                        if (is_array($productName)) {
                            continue;
                        }
                        $oneBanner["id"] = $each->getProCatId();
                        $oneBanner["name"] = $productName;
                    }
                    $banners[] = $oneBanner;
                }
                $oneCarousel["banners"] = $banners;
                if (count($oneCarousel["banners"])) {
                    $this->returnArray["carousel"][] = $oneCarousel;
                }

            }
        }
    }

    /**
     * Function to get Featured Categories
     * Set Featured categories to return array
     *
     * @return none
     */
    public function getFeaturedCategories()
    {
        $featuredCategoryCollection = $this->featuredCategories
            ->getCollection()
            ->addFieldToFilter("status", 1)
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => $this->storeId]
            ])
            ->setOrder("sort_order", "ASC");
        $featuredCategories = [];
        foreach ($featuredCategoryCollection as $eachCategory) {
            $newUrl = "";
            $dominantColorPath = "";
            $basePath = $this->baseDir.'/'.$eachCategory->getFilename();
            $oneCategory = [];
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir.'/'."mobikulresized".'/'.$this->iconWidth."x".
                    $this->iconHeight.'/'.$eachCategory->getFilename();
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $newUrl = $this->helper->getUrl("media")."mobikulresized".'/'.$this->iconWidth."x".
                    $this->iconHeight.'/'.$eachCategory->getFilename();
                $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".'/'.$this->iconWidth."x".
                    $this->iconHeight.'/'.$eachCategory->getFilename();
            }
            $oneCategory["url"] = $newUrl;
            $oneCategory["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
            $oneCategory["categoryId"] = $eachCategory->getCategoryId();
            $oneCategory["categoryName"] = $this->categoryResourceModel->getAttributeRawValue(
                $eachCategory->getCategoryId(),
                "name",
                $this->storeId
            );
            if (is_array($oneCategory["categoryName"])) {
                continue;
            }
            if ($eachCategory->getCategoryId()) {
                $featuredCategories[] = $oneCategory;
            }
        }
        $this->returnArray["featuredCategories"] = $featuredCategories;
    }

    /**
     * Function to get Featured deals
     * Set Featured deals to return array
     *
     * @return none
     */
    public function getFeaturedDeals()
    {
        $productList = [];
        $collection = new \Magento\Framework\DataObject();
        if ($this->helper->getConfigData("mobikul/configuration/featuredproduct") == 1) {
            $collection = $this->productCollection->create()->addAttributeToSelect(
                $this->catalogConfig->getProductAttributes()
            );
            $collection->getSelect()->order("rand()");
            $collection->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()]);
            $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
            if ($this->helperCatalog->showOutOfStock() == 0) {
                $this->stockFilter->addInStockFilterToCollection($collection);
            }
            $collection->setPage(1, 5)->load();
        } else {
            $collection = $this->productCollection->create()
                ->setStore($this->storeId)
                ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                ->addAttributeToSelect("as_featured")
                ->addAttributeToSelect("image")
                ->addAttributeToSelect("thumbnail")
                ->addAttributeToSelect("small_image")
                ->addAttributeToSelect("visibility")
                ->addStoreFilter()
                ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
                ->setVisibility($this->productVisibility->getVisibleInSiteIds())
                ->addAttributeToFilter("as_featured", 1);
            if ($this->helperCatalog->showOutOfStock() == 0) {
                $this->stockFilter->addInStockFilterToCollection($collection);
            }
            $collection->setPageSize(5)->setCurPage(1);
        }
        foreach ($collection as $eachProduct) {
            $productList[] = $this->helperCatalog->getOneProductRelevantData(
                $eachProduct,
                $this->storeId,
                $this->width
            );
        }
        $carousel = [];
        $carousel["id"] = "featuredProduct";
        $carousel["type"] = "product";
        $carousel["label"] = __("Featured Products");
        $carousel["productList"] = $productList;
        if (count($carousel["productList"])) {
            $this->returnArray["carousel"][] = $carousel;
        }
    }

    /**
     * Function to get New deals
     * Set New deals to return array
     *
     * @return none
     */
    public function getNewDeals()
    {
        $productList = [];
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $newProductCollection = $this->productCollection->create()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addStoreFilter()
            ->addMinimalPrice()
            ->addAttributeToFilter(
                "news_from_date",
                ["or"=>[
                    0=>["date"=>true, "to"=>$todayEndOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                "news_to_date",
                ["or"=>[
                    0=>["date"=>true, "from"=>$todayStartOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                [["attribute"=>"news_from_date", "is"=>new \Zend_Db_Expr("not null")],
                ["attribute"=>"news_to_date", "is"=>new \Zend_Db_Expr("not null")]]
            )
            ->addAttributeToSelect("image")
            ->addAttributeToSelect("thumbnail")
            ->addAttributeToSelect("small_image")
            ->addAttributeToSort("news_from_date", "desc");
        if ($this->helperCatalog->showOutOfStock() == 0) {
            $this->stockFilter->addInStockFilterToCollection($newProductCollection);
        }
        $newProductCollection->setPageSize(5)->setCurPage(1);
        foreach ($newProductCollection as $eachProduct) {
            $productList[] = $this->helperCatalog->getOneProductRelevantData(
                $eachProduct,
                $this->storeId,
                $this->width
            );
        }
        $carousel = [];
        $carousel["id"] = "newProduct";
        $carousel["type"] = "product";
        $carousel["label"] = __("New Products");
        $carousel["productList"] = $productList;
        if (count($carousel["productList"])) {
            $this->returnArray["carousel"][] = $carousel;
        }
    }

    /**
     * Function to get hot deals
     * Set Hot deals to return array
     *
     * @return none
     */
    public function getHotDeals()
    {
        $productList = [];
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $hotDealCollection = $this->productCollection->create()
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect("image")
            ->addAttributeToSelect("thumbnail")
            ->addAttributeToSelect("small_image")
            ->addAttributeToSelect("special_from_date")
            ->addAttributeToSelect("special_to_date")
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes());
        $hotDealCollection->addStoreFilter()
            ->addAttributeToFilter(
                "special_from_date",
                ["or"=>[
                    0=>["date"=>true, "to"=>$todayEndOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                "special_to_date",
                ["or"=>[
                    0=>["date"=>true, "from"=>$todayStartOfDayDate],
                    1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                [["attribute"=>"special_from_date", "is"=>new \Zend_Db_Expr("not null")],
                ["attribute"=>"special_to_date", "is"=>new \Zend_Db_Expr("not null")]]
            );
        if ($this->helperCatalog->showOutOfStock() == 0) {
            $this->stockFilter->addInStockFilterToCollection($hotDealCollection);
        }
        $hotDealCollection->setPageSize(5)->setCurPage(1);
        foreach ($hotDealCollection as $eachProduct) {
            $productList[] = $this->helperCatalog->getOneProductRelevantData(
                $eachProduct,
                $this->storeId,
                $this->width
            );
        }
        $carousel = [];
        $carousel["id"] = "hotDeals";
        $carousel["type"] = "product";
        $carousel["label"] = __("Hot Deals");
        $carousel["productList"] = $productList;
        if (count($carousel["productList"])) {
            $this->returnArray["carousel"][] = $carousel;
        }
    }

    /**
     * Function to get Banner Images
     * Set banner Images to return array
     *
     * @return none
     */
    protected function getBannerImages()
    {
        $collection = $this->bannerImage
            ->getCollection()
            ->addFieldToFilter("status", self::ENABLED)
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => $this->storeId]
            ])->setOrder("sort_order", "ASC");
        $bannerImages = [];
        foreach ($collection as $eachBanner) {
            $oneBanner = [];
            $newUrl = "";
            $dominantColorPath = "";
            $basePath = $this->baseDir.'/'.$eachBanner->getFilename();
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir.'/'."mobikulresized".'/'.$this->bannerWidth."x".
                    $this->height.'/'.$eachBanner->getFilename();
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->height);
                $newUrl = $this->helper->getUrl(
                    "media"
                )."mobikulresized".'/'.$this->bannerWidth."x".$this->height.'/'.$eachBanner->getFilename();
                $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".'/'.$this->bannerWidth."x".
                    $this->height.'/'.$eachBanner->getFilename();
            }
            $oneBanner["url"] = $newUrl;
            $oneBanner["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
            $oneBanner["bannerType"] = $eachBanner->getType();
            if ($eachBanner->getType() == "category") {
                $categoryName = $this->categoryResourceModel->getAttributeRawValue(
                    $eachBanner->getProCatId(),
                    "name",
                    $this->storeId
                );
                if (is_array($categoryName)) {
                    continue;
                }
                $oneBanner["id"] = $eachBanner->getProCatId();
                $oneBanner["name"] = $categoryName;
            } elseif ($eachBanner->getType() == "product") {
                $productName = $this->productResourceModel->getAttributeRawValue(
                    $eachBanner->getProCatId(),
                    "name",
                    $this->storeId
                );
                if (is_array($productName)) {
                    continue;
                }
                $oneBanner["id"] = $eachBanner->getProCatId();
                $oneBanner["name"] = $productName;
            }
            $bannerImages[] = $oneBanner;
        }
        $this->returnArray["bannerImages"] = $bannerImages;
    }

    /**
     * Function to get the layout options.
     *
     * @return array
     */
    public function getLayoutOptions()
    {
        try {
            $fixedlayout = [
                'featuredCategories',
                'bannerImages',
                'featuredProduct',
                'newProduct',
                'hotDeals'
            ];
            $arr = [];
            foreach ($this->returnArray as $key => $data) {
                if (($k = array_search($key, $fixedlayout)) !== false) {
                    unset($fixedlayout[$k]);
                }
                if ($key == 'featuredCategories') {
                    $arr[] = [
                        'id'=>'featuredcategories',
                        'type'=>'category',
                        'label'=>'Featured Categories'
                    ];
                }
                if ($key == 'bannerImages') {
                    $arr[] = [
                        'id'=>'bannerimage',
                        'type'=>'banner',
                        'label'=>'Banner Images'
                    ];
                }
                if ($key == 'carousel') {
                    $result = $this->getCarouselOption($data, $arr, $fixedlayout);
                    $fixedlayout = $result['fixedlayout'];
                    $arr = $result['arr'];
                }
            }

            foreach ($fixedlayout as $data) {
                $type=" ";
                $label = " ";
                if ($data == 'featuredCategories') {
                    $type="category";
                    $label = 'Featured Category';
                }
                if ($data == 'bannerImage') {
                    $type="banner";
                    $label = 'Banner';
                }
                if (in_array($data, ['featuredProduct', 'newProduct', 'hotDeals'])) {
                    $type="product";
                    $label = $data;
                }
                $arr[] = ['id'=>$data, 'type'=>$type, 'label'=>$label];
            }
                 
                return ['success' => true, 'data' => $arr];
            
        } catch (\Exception $e) {
            return ['success' => false, 'data'=> null];
        }
    }

    public function getCarouselOption($data, $arr, $fixedlayout)
    {
        foreach ($data as $key => $carouselData) {
            if (isset($carouselData['id'])) {
                if (($k = array_search($carouselData['id'], $fixedlayout)) !== false) {
                    unset($fixedlayout[$k]);
                }
                $arr[] = [
                    'id'=>$carouselData['id'],
                    'type'=>$carouselData['type'],
                    'label'=>$carouselData['label']
                ];
            } else {
                $id = str_replace(' ', '', strtolower($carouselData['label'])).'-'.$key;
                $arr[] = [
                    'id'=>$id,
                    'type'=>$carouselData['type'],
                    'label'=>$carouselData['label']
                ];
            }
        }
        return ['arr'=>$arr, 'fixedlayout'=>$fixedlayout];
    }

    /**
     * Function to get the image name according to the layout.
     *
     * @return string
     */
    public function getImageUrl($id = "dynamic", $type = "category")
    {
        switch (true) {
            case ($id == 'featuredcategories' && $type == 'category'):
                return 'category.jpg';
            case ($id == 'hotDeals' && $type == 'product'):
                return 'slide.jpg';
            case ($id == 'newDeals' && $type == 'product'):
                return 'grid.jpg';
            case ($id == 'newProduct' && $type == 'product'):
                return 'grid.jpg';
            case ($id == 'bannerimage' && $type == 'banner'):
                return 'banner.jpg';
            case ($type == 'product'):
                return 'grid.jpg';
            case ($type == 'image'):
                return 'slide.jpg';
            default:
                return 'grid.jpg';
        }
    }

    /**
     * Function to get the mobile view layout.
     *
     * @return array
     */
    public function getMobileViewLayout()
    {
        $mobileViewData = [];
        $tempArr = [];
        for ($i=1; $i<=self::MAX_LAYOUT_NO; $i++) {
            $model = $this->_appcreatorFactory->create();
            $model->load($i);
            $mData = $model->getData();
            if (empty($mData)) {
                continue;
            }
            $position = explode(',', trim($mData['position'], ','));
            foreach ($position as $key => $data) {
                $tempArr[$data] = $mData;
            }
        }
        $arr = [];
        $a = 1;
        for ($i = 1; $i <=self::MAX_LAYOUT_NO; $i++) {
            if (!array_key_exists($i, $tempArr)) {
                continue;
            }
            $arr [$a++] = $tempArr[$i];
        }
        foreach ($arr as $data) {
            $mobileViewData[] = [
                'imagePath' => $this->getImageUrl($data['layout_id'], $data['type']),
                'label' => rawurldecode($data['label']) // @codingStandardsIgnoreLine
            ];
        }
        return $mobileViewData;
    }

    /**
     * Function to get the selected layout.
     *
     * @return array
     */
    public function getSelectedLayout()
    {
        try {
            $tempArr = [];
            for ($i=1; $i<=self::MAX_LAYOUT_NO; $i++) {

                $model = $this->_appcreatorFactory->create();
                $model->load($i);
                $mData = $model->getData();
                if (empty($mData)) {
                    continue;
                }
                $position = explode(',', trim($mData['position'], ','));
                foreach ($position as $key => $data) {
                    $tempArr[$data] = $mData;
                }
            }
            $arr = [];
            $a = 1;
            for ($i = 1; $i <=self::MAX_LAYOUT_NO; $i++) {
                if (!array_key_exists($i, $tempArr)) {
                    continue;
                }
                $arr [$a++] = $tempArr[$i];
            }
            return $arr;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Function to get the maximum no. of layout to be added.
     *
     * @return int
     */
    public function getMaxLayout()
    {
        return self::MAX_LAYOUT_NO;
    }
}
