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

/**
 * HomePageData Class api
 */
class HomePageData extends AbstractCatalog
{
    const ENABLED = true;
    /**
     * Execute function for HomePageData Class
     *
     * @return array
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            if (!$this->customerId && $this->customerToken != "") {
                return $this->getJsonResponse($this->returnArray);
            }
            $cacheString = "HOMEPAGEDATA".$this->url.$this->width.$this->quoteId.
            $this->storeId.$this->mFactor.$this->websiteId.$this->isFromUrl.
            $this->customerToken.$this->currency;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            if ($this->storeId == 0) {
                $this->storeId = $this->websiteManager->create()->load($this->websiteId)->getDefaultGroup()
                    ->getDefaultStoreId();
                $this->returnArray["storeId"] = $this->storeId;
            }
            $this->bannerWidth = $this->helper->getValidDimensions($this->mFactor, $this->width);
            $this->height = $this->helper->getValidDimensions($this->mFactor, 2*($this->width/3));
            $this->iconHeight = $this->iconWidth = $this->helper->getValidDimensions($this->mFactor, 288);
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Getting currency data ////////////////////////////////////////////////
            $currencies = [];
            $this->returnArray["allowedCurrencies"] = $this->storeInterface->getStore()
                ->getAvailableCurrencyCodes(false);
            foreach ($this->storeInterface->getStore()->getAvailableCurrencyCodes(false) as $code) {
                $currencies[] =[
                    "label" => $this->currencyInterface->getCurrency($code)->getSymbol()." ".
                        $this->currencyInterface->getCurrency($code)->getName(),
                    "code" => $code
                ];
            }
            if ($this->currency == "") {
                $this->currency = $this->store->getCurrentCurrencyCode();
            }
            $this->returnArray["allowedCurrencies"] = $currencies;
            $this->store->setCurrentCurrencyCode($this->currency);
            $this->returnArray["defaultCurrency"] = $this->helper->getConfigData("currency/options/default");
            $this->returnArray["allowIosDownload"] = (bool)$this->helper->getConfigData("mobikul/appdownload/allowiOS");
            $this->returnArray["iosDownloadLink"] = $this->helper->getConfigData("mobikul/appdownload/ioslink");
            $this->returnArray["allowAndroidDownload"] = (bool)$this->helper->getConfigData(
                "mobikul/appdownload/allowAndroid"
            );
            $this->returnArray["androidDownloadLink"] = $this->helper->getConfigData("mobikul/appdownload/androidlink");
            // Checking is swatch allowed on colletion page /////////////////////////
            $this->returnArray["showSwatchOnCollection"] = (bool)$this->helper->getConfigData(
                "catalog/frontend/show_swatches_in_product_list"
            );
            // Getting price format /////////////////////////////////////////////////
            $this->returnArray["priceFormat"] = $this->localeFormat->getPriceFormat();
            // Precessing deep linking //////////////////////////////////////////////
            $this->processDeepLinking();
            // Theme Code of the application ////////////////////////////////////////
            $this->returnArray["themeCode"] = $this->helper->getConfigData("mobikul/theme/code");
            // Category data for drawer menu ////////////////////////////////////////
            $this->getCategoriesData();

            $this->returnArray["wishlistEnable"] = (bool)$this->helper->getConfigData("wishlist/general/active");
            // Featured Category ////////////////////////////////////////////////////
            $this->getFeaturedCategories();
            // Banner Images ////////////////////////////////////////////////////////
            $this->getBannerImages();
            // Featured Products ////////////////////////////////////////////////////
            $this->getFeaturedDeals();
            // New Deals ////////////////////////////////////////////////////////////
            $this->getNewDeals();
            // Hot Deals ////////////////////////////////////////////////////////////
            $this->getHotDeals();
            // Getting all image and product carousel data //////////////////////////
            $this->getImageNProductCarousel();
            // Store Data ///////////////////////////////////////////////////////////
            $this->returnArray["websiteData"] = $this->helperCatalog->getWebsiteData();
            $this->returnArray["storeData"] = $this->helperCatalog->getStoreData($this->websiteId);
            // Customer Profile and Banner Images ///////////////////////////////////
            $this->getCustomerImages();
            // Getting CMS page data ////////////////////////////////////////////////
            $this->getCmsData();
            // Cart Count ///////////////////////////////////////////////////////////
            if ($this->quoteId != 0) {
                $this->returnArray["cartCount"] = $this->helper->getCartCount($this->quoteId);
            }
            $this->getSortingOrder();
            $this->returnArray["themeType"] = (int)$this->helper->getConfigData("mobikul/themeConfig/themeType");
            $this->returnArray["success"] = true;
            $this->customerSession->setCustomerId(null);
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to process deep linking
     *
     * @return string
     */
    protected function processDeepLinking()
    {
        if ($this->isFromUrl != 0) {
            $baseurl = $this->storeInterface->getStore($this->storeId)->getBaseUrl();
            $urlArray = explode($baseurl, $this->url);
            if (count($urlArray) > 1) {
                $itemFound = $this->getDataFromUrl($urlArray[1]);
                if ($itemFound) {
                    if ($itemFound["entity_type"] == "product") {
                        $this->returnArray["productId"] = $itemFound["entity_id"];
                        $this->returnArray["productName"] = $this->productResourceModel
                            ->getAttributeRawValue(
                                $this->returnArray["productId"],
                                "name",
                                $this->storeId
                            );
                        if (is_array($this->returnArray["productName"])) {
                            $this->returnArray["productName"] = "";
                        }
                        $this->returnArray["success"] = true;
                        return $this->getJsonResponse($this->returnArray);
                    } elseif ($itemFound["entity_type"] == "category") {
                        $this->returnArray["categoryId"] = $itemFound["entity_id"];
                        $this->returnArray["categoryName"] = $this->categoryResourceModel
                            ->getAttributeRawValue(
                                $this->returnArray["categoryId"],
                                "name",
                                $this->storeId
                            );
                        if (is_array($this->returnArray["categoryName"])) {
                            $this->returnArray["categoryName"] = "";
                        }
                        $this->returnArray["success"] = true;
                        return $this->getJsonResponse($this->returnArray);
                    } elseif ($itemFound["entity_type"] == "cms-page") {
                        $this->returnArray["title"] = $this->cmsPage->load($itemFound["entity_id"])->getTitle();
                        $this->returnArray["pageId"] = $itemFound["entity_id"];
                        $this->returnArray["success"] = true;
                        return $this->getJsonResponse($this->returnArray);
                    }
                }
            } else {
                $this->returnArray["message"] = __("Sorry, something went wrong.");
                return $this->getJsonResponse($this->returnArray);
            }
        }
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET") {
            $this->url = $this->wholeData["url"] ?? "";
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->mFactor = $this->helper->calcMFactor($this->mFactor);
            $this->websiteId = $this->wholeData["websiteId"] ?? 1;
            $this->isFromUrl = $this->wholeData["isFromUrl"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->currency = $this->wholeData["currency"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if ($this->websiteId == 0 && $this->storeId == 0) {
                throw new \BadMethodCallException(__("Invalid Website Id"));
            }
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["success"] = false;
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            } elseif ($this->customerId != 0) {
                $this->customer = $this->customerFactory->create()->load($this->customerId);
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to get Image and Product Carousel
     * Set carousels to return array
     *
     * @return none
     */
    protected function getImageNProductCarousel()
    {
        $collection = $this->carouselFactory->create()->getCollection()
            ->addFieldToFilter("status", 1)
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => $this->storeId]
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
                        $this->width,
                        $this->customerId
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
                    $basePath = $this->baseDir.DS.$each->getFilename();
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir.DS."mobikulresized".DS.$this->bannerWidth."x".
                            $this->height.DS.$each->getFilename();
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->height);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->bannerWidth."x".
                            $this->height.DS.$each->getFilename();
                        $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.
                            $this->bannerWidth."x".$this->height.DS.$each->getFilename();
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
    protected function getFeaturedCategories()
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
            $basePath = $this->baseDir.DS.$eachCategory->getFilename();
            $oneCategory = [];
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir.DS."mobikulresized".DS.$this->iconWidth."x".
                    $this->iconHeight.DS.$eachCategory->getFilename();
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->iconWidth, $this->iconHeight);
                $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                    $this->iconHeight.DS.$eachCategory->getFilename();
                $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.$this->iconWidth."x".
                    $this->iconHeight.DS.$eachCategory->getFilename();
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
            $basePath = $this->baseDir.DS.$eachBanner->getFilename();
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir.DS."mobikulresized".DS.$this->bannerWidth."x".
                    $this->height.DS.$eachBanner->getFilename();
                $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->height);
                $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->bannerWidth."x".
                    $this->height.DS.$eachBanner->getFilename();
                $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.$this->bannerWidth."x".
                    $this->height.DS.$eachBanner->getFilename();
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
     * Function to get Featured deals
     * Set Featured deals to return array
     *
     * @return none
     */
    protected function getFeaturedDeals()
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
                $this->width,
                $this->customerId
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
    protected function getNewDeals()
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
                $this->width,
                $this->customerId
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
    protected function getHotDeals()
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
                $this->width,
                $this->customerId
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
     * Function to add category data to return array
     *
     * @return null
     */
    protected function getCategoriesData()
    {
        $categoryImages = $this->getCategoryImages();
        $catCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect("*")
            ->addFieldToFilter("is_active", ["eq" => 1])
            ->addFieldToFilter("include_in_menu", ["eq" => 1])
            ->addFieldToFilter("level", "2")
            ->addAttributeToSort('position', 'ASC');
        $categories = [];
        foreach ($catCollection as $cc) {
            if (array_key_exists($cc->getEntityId(), $categoryImages)) {
                $categories[] = [
                    "id" => $cc->getEntityId(),
                    "name" => $cc->getName(),
                    "banner" => (array_key_exists("banner", $categoryImages[$cc->getEntityId()])) ? : "",
                    "thumbnail" => $categoryImages[$cc->getEntityId()]["thumbnail"],
                    "hasChildren" => $cc->getChildrenCount() > 0 ? true:false,
                    "bannerDominantColor" => (array_key_exists("bannerDominantColor", $categoryImages[$cc->getEntityId()])) ? : "",
                    "thumbnailDominantColor" => $categoryImages[$cc->getEntityId()]["thumbnailDominantColor"]
                ];
            } else {
                $categories[] = [
                    "id" => $cc->getEntityId(),
                    "name" => $cc->getName(),
                    "hasChildren" => $cc->getChildrenCount() > 0 ? true:false
                ];
            }
        }
        $this->returnArray["categories"] = $categories;
    }

    public function getCategoryChildren($categories)
    {
        $categoryImages = $this->getCategoryImages();
        foreach ($categories as $key => $category) {
            if (array_key_exists($category["category_id"], $categoryImages)) {
                $category["banner"] = $categoryImages[$category["category_id"]]["banner"];
                $category["thumbnail"] = $categoryImages[$category["category_id"]]["thumbnail"];
                $category["bannerDominantColor"] = $categoryImages[$category["category_id"]]["bannerDominantColor"];
                $category["thumbnailDominantColor"] = $categoryImages[
                    $category["category_id"]
                ]["thumbnailDominantColor"];
            }
            if (count($category["children"]) > 0) {
                $category["children"] = $this->getCategoryChildren($category["children"]);
            }
            $categories[$key] = $category;
        }
        return $categories;
    }

    /**
     * Function to get categories Images
     *
     * @return array
     */
    protected function getCategoryImages()
    {
        $categoryImages = [];
        $categoryImgCollection = $this->categoryImageFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => $this->storeId]
            ]);
        foreach ($categoryImgCollection as $categoryImage) {
            $eachCategoryImage["id"] = $categoryImage->getCategoryId();
            if ($categoryImage->getIcon() != "") {
                $basePath = $this->baseDir.DS."mobikul".DS."categoryimages".DS."icon".DS.$categoryImage->getIcon();
                $newUrl = "";
                $dominantColorPath = "";
                if ($this->fileDriver->isFile($basePath)) {
                    $newPath = $this->baseDir.DS."mobikulresized".DS."144x144".DS.
                        "categoryimages".DS."icon".DS.$categoryImage->getIcon();
                    $this->helperCatalog->resizeNCache($basePath, $newPath, 144, 144);
                    $newUrl = $this->helper->getUrl("media")."mobikulresized".DS."144x144".DS.
                        "categoryimages".DS."icon".DS.$categoryImage->getIcon();
                    $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS."144x144".
                        DS."categoryimages".DS."icon".DS.$categoryImage->getIcon();
                }
                $eachCategoryImage["thumbnail"] = $newUrl;
                $eachCategoryImage["thumbnailDominantColor"] = $this->helper->getDominantColor($dominantColorPath);
            }
            if ($categoryImage->getBanner() != "") {
                $basePath = $this->baseDir.DS."mobikul".DS."categoryimages".DS."banner".DS.
                    $categoryImage->getBanner();
                $newUrl = "";
                $dominantColorPath = "";
                if ($this->fileDriver->isFile($basePath)) {
                    $newPath = $this->baseDir.DS."mobikulresized".DS.$this->bannerWidth."x".
                        $this->height.DS."categoryimages".DS."banner".DS.$categoryImage->getBanner();
                    $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->height);
                    $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->bannerWidth."x".
                        $this->height.DS."categoryimages".DS."banner".DS.$categoryImage->getBanner();
                    $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.
                        $this->bannerWidth."x".$this->height.DS."categoryimages".DS."banner".DS.
                        $categoryImage->getBanner();
                }
                $eachCategoryImage["banner"] = $newUrl;
                $eachCategoryImage["bannerDominantColor"] = $this->helper->getDominantColor($dominantColorPath);
            }

            $categoryImages[$eachCategoryImage["id"]] = $eachCategoryImage;
        }
        return $categoryImages;
    }

    /**
     * Function to get customer Images
     *
     * @return none
     */
    protected function getCustomerImages()
    {
        if ($this->customerId != 0) {
            $this->returnArray["customerName"] = $this->customer->getName();
            $this->returnArray["customerEmail"] = $this->customer->getEmail();
            $quote = $this->helper->getCustomerQuote($this->customerId);
            $this->returnArray["cartCount"] = $this->helper->getCartCount($quote);
            $collection = $this->customerImage->getCollection()->addFieldToFilter("customer_id", $this->customerId);
            $time = time();
            if ($collection->getSize() > 0) {
                foreach ($collection as $value) {
                    if ($value->getBanner() != "") {
                        $basePath = $this->baseDir.DS."mobikul".DS."customerpicture".DS.
                            $this->customerId.DS.$value->getBanner();
                        $newUrl = "";
                        $dominantColorPath = "";
                        if ($this->fileDriver->isFile($basePath)) {
                            $newPath = $this->baseDir.DS."mobikulresized".DS.$this->bannerWidth."x".
                                $this->height.DS."customerpicture".DS.$this->customerId.DS.$value->getBanner();
                            $this->helperCatalog->resizeNCache($basePath, $newPath, $this->bannerWidth, $this->height);
                            $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->bannerWidth."x".
                                $this->height.DS."customerpicture".DS.$this->customerId.DS.$value->getBanner();
                            $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.
                                $this->bannerWidth."x".$this->height.DS."customerpicture".DS.$this->customerId.DS.
                                $value->getBanner();
                        }
                        $this->returnArray["customerBannerImage"] = $newUrl."?".$time;
                        $this->returnArray["bannerDominantColor"] = $this->helper->getDominantColor(
                            $dominantColorPath."?".$time
                        );
                    }
                    if ($value->getProfile() != "") {
                        $basePath = $this->baseDir.DS."mobikul".DS."customerpicture".DS.
                            $this->customerId.DS.$value->getProfile();
                        $newUrl = "";
                        $dominantColorPath = "";
                        if ($this->fileDriver->isFile($basePath)) {
                            $newPath = $this->baseDir.DS."mobikulresized".DS.$this->iconWidth."x".
                                $this->iconHeight.DS."customerpicture".DS.$this->customerId.DS.$value->getProfile();
                            $this->helperCatalog->resizeNCache(
                                $basePath,
                                $newPath,
                                $this->iconWidth,
                                $this->iconHeight
                            );
                            $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$this->iconWidth."x".
                                $this->iconHeight.DS."customerpicture".DS.$this->customerId.DS.$value->getProfile();
                            $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.
                                $this->iconWidth."x".$this->iconHeight.DS."customerpicture".DS.$this->customerId.DS.
                                $value->getProfile();
                        }
                        $this->returnArray["customerProfileImage"] = $newUrl."?".$time;
                        $this->returnArray["customerDominantColor"] = $this->helper->getDominantColor(
                            $dominantColorPath."?".$time
                        );
                    }
                }
            }
        }
    }

    /**
     * Get Cms Data to get CMS data for the Module
     *
     * @return array
     */
    protected function getCmsData()
    {
        $cmsData = [];
        $allowedCmsPages = $this->helper->getConfigData("mobikul/configuration/cms");
        if ($allowedCmsPages != "") {
            $allowedIds = explode(",", $allowedCmsPages);
            $storeIds = explode(',', $this->storeId);
            array_push($storeIds, 0);
            $collection = $this->cmsCollection
                ->addFieldToFilter("is_active", \Magento\Cms\Model\Page::STATUS_ENABLED)
                ->addFieldToFilter("store_id", ["in"=>$storeIds])
                ->addFieldToFilter("page_id", ["in"=>$allowedIds]);
            foreach ($collection as $cms) {
                $cmsData[] = ["id"=>$cms->getId(), "title"=>$cms->getTitle()];
            }
        }
        $this->returnArray["cmsData"] = $cmsData;
    }

    /**
     * Get data from url function
     *
     * @param string $url url
     *
     * @return array
     */
    private function getDataFromUrl($url)
    {
        $path = [$url];
        $pathBind = [];
        foreach ($path as $key => $url) {
            $pathBind["path".$key] = $url;
        }
        $queryType = 'select';
        $tableName = $this->connection->getTableName("url_rewrite");
        $sql = "$queryType * from ".$tableName." where request_path IN (:".
            implode(", :", array_flip($pathBind)).") AND store_id IN(0,".$this->storeId.")";
        $items = $this->connection->getConnection()->fetchAll($sql, $pathBind);
        $foundItem = null;
        $mapPenalty = array_flip(array_values($path));
        $currentPenalty = null;
        foreach ($items as $item) {
            if (!array_key_exists($item["request_path"], $mapPenalty)) {
                continue;
            }
            $penalty = $mapPenalty[$item["request_path"]] << 1 + ($item["store_id"] ? 0 : 1);
            if (!$foundItem || $currentPenalty > $penalty) {
                $foundItem = $item;
                $currentPenalty = $penalty;
                if (!$currentPenalty) {
                    break;
                }
            }
        }
        return $foundItem;
    }

    /**
     * Get sort order of data.
     *
     *
     * @return array
     */
    private function getSortingOrder()
    {
        $appCreatorModal = $this->appcreatorFactory->create();
        $size = $appCreatorModal->getCollection()->getSize();
        if ($size > 0) {
            $this->returnArray['sort_order'] = $appCreatorModal->getCollection()->getData();
            foreach ($this->returnArray['sort_order'] as &$data) {
                if ($data['type'] == "image") {
                    $id = explode("-", $data['layout_id']);
                    if (end($id) != "") {
                        $data['layout_id'] = end($id);
                    }
                }
            }
        } else {
            $this->getShortOrder();
        }
    }

    protected function getShortOrder()
    {
        $count = 1;
        $arr = [];
        $fixedlayout = ['featuredCategories', 'bannerImages', 'featuredProduct', 'newProduct', 'hotDeals'];
        foreach ($this->returnArray as $key => $data) {
            if (($k = array_search($key, $fixedlayout)) !== false) {
                unset($fixedlayout[$k]);
            }
            if ($key == 'featuredCategories') {
                $arr[] = [
                    'id'=>'featuredcategories',
                    'layout_id'=>'featuredcategories',
                    'type'=>'category',
                    'position'=>$count++
                ];
            }
            if ($key == 'bannerImages') {
                $arr[] = [
                    'id'=>'bannerimage',
                    'layout_id'=>'bannerimage',
                    'type'=>'banner',
                    'position'=>$count++
                ];
            }
            if ($key == 'carousel') {
                foreach ($data as $key => $carouselData) {
                    if (isset($carouselData['id'])) {
                        if (($k = array_search($carouselData['id'], $fixedlayout)) !== false) {
                            unset($fixedlayout[$k]);
                        }
                        $arr[] = [
                            'id'=>$carouselData['id'],
                            'layout_id'=>$carouselData['id'],
                            'type'=>$carouselData['type'],
                            'position' => $count++
                        ];
                    } else {
                        $id = $key;
                        $arr[] = [
                            'id'=>$id,
                            'layout_id'=>$carouselData['id'],
                            'type'=>$carouselData['type'],
                            'position' => $count++
                        ];
                    }
                }
            }
        }
        foreach ($fixedlayout as $data) {
            $type=" ";
            if ($data == 'featuredCategories') {
                $type="category";
            }
            if ($data == 'bannerImage') {
                $type="banner";
            }
            if (in_array($data, ['featuredProduct', 'newProduct', 'hotDeals'])) {
                $type="product";
            }
            $arr[] = ['id'=>$data, 'layout_id'=>$data, 'type'=>$type, 'position'=>$count++];
        }
        $this->returnArray['sort_order'] = $arr;
    }
}
