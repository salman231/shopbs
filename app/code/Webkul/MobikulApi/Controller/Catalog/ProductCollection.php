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
 * Class CategoryProductList api
 */
class ProductCollection extends AbstractCatalog
{
    /**
     * Execute function for class CategoryProductList
     *
     * @return json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "PRODUCTCOLLECTION".$this->width.$this->storeId.$this->type.$this->id.
            $this->quoteId.$this->mFactor.$this->pageNumber.
            $this->id.$this->customerToken.$this->currency;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->sortData = $this->jsonHelper->jsonDecode($this->sortData);
            $this->filterData = $this->jsonHelper->jsonDecode($this->filterData);
            // Setting currency /////////////////////////////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($this->currency);
            if ($this->type == "customCarousel") {
                switch ($this->id) {
                    case "featuredProduct":
                        $this->getFeaturedProductCollection();
                        break;
                    case "newProduct":
                        $this->getNewProductCollection();
                        break;
                    case "hotDeals":
                        $this->getHotDealsCollection();
                        break;
                    default:
                        $this->getCarouselProductCollection();
                        break;
                }
            } elseif ($this->type == "search") {
                $isFlatEnabled = $this->productResourceCollection->isEnabledFlat();
                $this->getRequest()->setParam("q", $this->id);
                $query = $this->queryFactory->get();
                $query->setStoreId($this->storeId);
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity()+1);
                } else {
                    $query->setQueryText($this->id)
                        ->setIsActive(1)
                        ->setPopularity(1)
                        ->setIsProcessed(1)
                        ->setDisplayInTerms(1);
                }
                $query->prepare()->save();
                $this->collection = $this->helperCatalog->getProductListColl(
                    $this->storeInterface->getStore()->getRootCategoryId(),
                    "search"
                );
            } elseif ($this->type == "advSearch") {
                $this->sortData = "{}";
                $this->filterData = "{}";
                $this->sortData = $this->jsonHelper->jsonDecode($this->sortData);
                $this->filterData = $this->jsonHelper->jsonDecode($this->filterData);
                
                $this->queryArray = $this->jsonHelper->jsonDecode($this->id);
                $this->queryArray = $this->helperCatalog->getQueryArray($this->queryArray);
                $advancedSearch = $this->advancedCatalogSearch->addFilters($this->queryArray);
                $this->collection = $advancedSearch->getProductCollection();
                $criteriaData    = [];
                $searchCriterias = $this->getSearchCriterias($advancedSearch->getSearchCriterias());
                foreach (["left", "right"] as $side) {
                    if ($searchCriterias[$side]) {
                        foreach ($searchCriterias[$side] as $criteria) {
                            $criteriaData[] = $this->helperCatalog->stripTags(__($criteria["name"]))." : ".
                                $this->helperCatalog->stripTags($criteria["value"]);
                        }
                    }
                }
                $this->returnArray["criteriaData"] = $criteriaData;
            } elseif ($this->type == "carousel") {
                $this->getCarouselProductCollection();
            } elseif ($this->type == "customCollection") {
                $this->notification = $this->mobikulNotification->create()->load($this->id);
                $customFilterData = $this->serializer->unserialize($this->notification->getFilterData());
                $notificationCollectionType = $this->notification->getCollectionType();
                $this->getCustomNotificationCollection($notificationCollectionType, $customFilterData);
            } else {
                // Creating product collection /////////////////////////////////////
                $this->loadedCategory = $this->category->create()->setStoreId($this->storeId)->load($this->id);
                $this->coreRegistry->register("current_category", $this->loadedCategory);
                $this->collection = $this->helperCatalog->getProductListColl($this->id);
                $this->collection->addAttributeToSelect("*");

                $categoryToFilter = $this->category->create()->load($this->id);
                $this->collection->setStoreId($this->storeId)->addCategoryFilter($categoryToFilter);
            }
            if ($this->collection && $this->helperCatalog->showOutOfStock() == 0) {
                $this->stockFilter->addInStockFilterToCollection($this->collection);
            }
            // Filtering product collection /////////////////////////////////////////
            $this->filterProductCollection();
            // Sorting product collection ///////////////////////////////////////////
            $this->sortProductCollection();
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                if ($this->collection) {
                    $this->collection->distinct(true);
                    $this->returnArray["totalCount"] = $this->collection->getSize();
                } else {
                    $this->returnArray["totalCount"] = 0;
                }
                $pageSize = $this->helperCatalog->getPageSize();
                if ($this->collection) {
                    $this->collection->setPageSize($pageSize)->setCurPage($this->pageNumber);
                }
            }
            // Creating product collection //////////////////////////////////////////
            $productList = [];
            if ($this->collection) {
                $this->collection->addMinimalPrice();
                foreach ($this->collection as $eachProduct) {
                    $productList[] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $this->storeId,
                        $this->width,
                        $this->customerId
                    );
                }
            }
            $this->returnArray["productList"] = $productList;
            // Creating filter attribute collection /////////////////////////////////
            $this->getLayeredData();
            // Creating sort attribute collection ///////////////////////////////////
            $this->getSortingData();
            // Cart Count ///////////////////////////////////////////////////////////
            if ($this->quoteId != 0) {
                $this->returnArray["cartCount"] = $this->helper->getCartCount(
                    $this->quoteModel->setStoreId($this->storeId)->load($this->quoteId)
                );
            }
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
                $this->returnArray["cartCount"] = $this->helper->getCartCount($quote);
            }
            // Getting category banner image ////////////////////////////////////////
            if ($this->type == "category") {
                $this->getCategoryImages();
            }
            $this->returnArray["success"] = true;
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
     * Function to verify request
     *
     * @return json|void
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->id = $this->wholeData["id"] ?? 0;
            $this->type = $this->wholeData["type"] ?? "category";
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->mFactor = $this->helper->calcMFactor($this->mFactor);
            $this->sortData = $this->wholeData["sortData"] ?? "[]";
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->filterData = $this->wholeData["filterData"] ?? "[]";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->currency = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to get featured Product Collection
     *
     * @return void
     */
    protected function getFeaturedProductCollection()
    {
        $this->collection = $this->productCollection->create()
            ->setStore($this->storeId)
            ->addAttributeToSelect("*")
            ->addAttributeToSelect("as_featured")
            ->addAttributeToSelect("visibility")
            ->addStoreFilter()
            ->addAttributeToFilter("status", ["in" => $this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInSiteIds());
        if ($this->helper->getConfigData("mobikul/configuration/featuredproduct") == 1 && empty($this->sortData)) {
            $this->collection->getSelect()->order("rand()");
        } elseif (!$this->helper->getConfigData("mobikul/configuration/featuredproduct")) {
            $this->collection->addAttributeToFilter("as_featured", 1);
        }
    }

    /**
     * Function to get New Product Collection
     *
     * @return void
     */
    protected function getNewProductCollection()
    {
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $this->collection = $this->productCollection->create()
            ->addAttributeToSelect("*")
            ->addStoreFilter()
            ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addAttributeToFilter(
                "news_from_date",
                [
                    "or"=>[
                        0=>["date"=>true, "to"=>$todayEndOfDayDate],
                        1=>["is"=>new \Zend_Db_Expr("null")]]
                ],
                "left"
            )
            ->addAttributeToFilter(
                "news_to_date",
                [
                    "or"=>[
                        0=>["date"=>true, "from"=>$todayStartOfDayDate],
                        1=>["is"=>new \Zend_Db_Expr("null")]
                    ]
                ],
                "left"
            )
            ->addAttributeToFilter(
                [
                    ["attribute"=>"news_from_date", "is"=>new \Zend_Db_Expr("not null")],
                    ["attribute"=>"news_to_date", "is"=>new \Zend_Db_Expr("not null")]
                ]
            );
    }

    /**
     * Function to get Hot Deals Product Collection
     *
     * @return void
     */
    protected function getHotDealsCollection()
    {
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $this->collection = $this->productCollection->create()
            ->addAttributeToSelect("*")
            ->addStoreFilter()
            ->addAttributeToFilter(
                "status",
                [
                    "in"=>$this->productStatus->getVisibleStatusIds()
                ]
            )
            ->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addAttributeToFilter(
                "special_from_date",
                [
                    "or"=>[
                        0=>["date"=>true, "to"=>$todayEndOfDayDate],
                        1=>["is"=>new \Zend_Db_Expr("null")]
                    ]
                ],
                "left"
            )
            ->addAttributeToFilter(
                "special_to_date",
                [
                    "or"=>[
                        0=>["date"=>true, "from"=>$todayStartOfDayDate],
                        1=>["is"=>new \Zend_Db_Expr("null")]
                    ]
                ],
                "left"
            )
            ->addAttributeToFilter(
                [
                    ["attribute"=>"special_from_date", "is"=>new \Zend_Db_Expr("not null")],
                    ["attribute"=>"special_to_date", "is"=>new \Zend_Db_Expr("not null")]
                ]
            );
    }

    /**
     * Function to get carousel collection
     *
     * @return void
     */
    protected function getCarouselProductCollection()
    {
        $carouselId = $this->id;
        $productIdsArray = [];
        $productIds = $this->carouselFactory->create()->load($carouselId)->getProductIds();
        if ($productIds!= "") {
            $productIdsArray = explode(",", $productIds);
        }
        $this->collection = $this->productFactory->create()->getCollection()
            ->addFieldToSelect("*")
            ->addFieldToFilter("entity_id", ["in"=> $productIdsArray])
            ->addAttributeToFilter(
                "status",
                [
                    "in"=>$this->productStatus->getVisibleStatusIds()
                ]
            )
            ->setVisibility($this->productVisibility->getVisibleInSiteIds());
    }

    /**
     * Function to filter Product Collection
     *
     * @return void
     */
    protected function filterProductCollection()
    {
        if (count($this->filterData) > 0) {
            $filterCount = count($this->filterData[0]);
            for ($i=0; $i<$filterCount; ++$i) {
                if ($this->filterData[0][$i] != "" && $this->filterData[1][$i] == "price") {
                    $priceRange = explode("-", $this->filterData[0][$i]);
                    $currencyRate = $this->collection->getCurrencyRate();
                    list($from, $to) = $priceRange;
                    $this->collection->addFieldToFilter(
                        "price",
                        ["from"=>$from, "to"=>empty($to) || $from == $to ? $to : $to - 0.001]
                    );
                    $this->catalogLayer->getState()->addFilter(
                        $this->helperCatalog->_createItem(empty($from) ? 0 : $from, $to, $priceRange)
                    );
                } elseif ($this->filterData[0][$i] != "" && $this->filterData[1][$i] == "cat") {
                    $categoryToFilter = $this->category->create()->load($this->filterData[0][$i]);
                    $this->collection->setStoreId($this->storeId)->addCategoryFilter($categoryToFilter);
                } else {
                    $attribute = $this->eavConfig->getAttribute("catalog_product", $this->filterData[1][$i]);
                    $attributeModel = $this->layerAttribute->create()->setAttributeModel($attribute);
                    $this->collection->addFieldToFilter($attribute->getAttributeCode(), $this->filterData[0][$i]);
                    $this->catalogLayer
                        ->getState()
                        ->addFilter($this->helperCatalog->_createItem(
                            $this->filterData[0][$i],
                            $this->filterData[0][$i]
                        ));
                }
            }
        }
    }

    /**
     * Function to getCustomCollection
     *
     * @return void
     */
    public function getCustomNotificationCollection($type, $customFilterData)
    {
        $bannerWidth = $this->helper->getValidDimensions($this->mFactor, $this->width);
        $bannerHeight = $this->helper->getValidDimensions($this->mFactor, 2*($this->width/3));
        $bannerUrl = "";
        $dominantColorPath = "";
        if ($this->notification->getFilename() != "") {
            $basePath = $this->baseDir.DS."mobikul".DS."notification".DS.$this->notification->getFilename();
            if ($this->fileDriver->isFile($basePath)) {
                $newPath = $this->baseDir.DS."mobikulresized".DS.$bannerWidth."x".$bannerHeight.DS.
                    "notification".DS.$this->notification->getFilename();
                $this->helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                $bannerUrl = $this->helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".
                    $bannerHeight.DS."notification".DS.$this->notification->getFilename();
                $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.$bannerWidth."x".
                    $bannerHeight.DS."notification".DS.$this->notification->getFilename();
            }
        }
        $this->returnArray["bannerImage"] = $bannerUrl;
        $this->returnArray["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
        $productCollection = $this->productCollection->create();
        if ($type == "product_attribute") {
            $productCollection->setStore($this->storeId)
                ->addAttributeToSelect("*")
                ->addAttributeToSelect("as_featured")
                ->addAttributeToSelect("visibility")
                ->addStoreFilter()
                ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
                ->setVisibility($this->productVisibility->getVisibleInSiteIds());
            foreach ($customFilterData as $key => $filterValue) {
                if ($key == "category_ids") {
                    foreach (explode(",", $filterValue) as $value) {
                        $productCollection->addCategoryFilter($this->categoryFactory->create()->load($value));
                    }
                } else {
                    $productCollection->addAttributeToSelect($key);
                    $productCollection->addAttributeToFilter($key, ["in" => $filterValue]);
                }
            }
        } elseif ($type == "product_ids") {
            $productCollection->setStore($this->storeId)
                ->addAttributeToSelect("*")
                ->addAttributeToSelect("as_featured")
                ->addAttributeToSelect("visibility")
                ->addStoreFilter()
                ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
                ->setVisibility($this->productVisibility->getVisibleInSiteIds());
            $productCollection->addAttributeToFilter("entity_id", ["in" => explode(",", $customFilterData)]);
        } elseif ($type == "product_new") {
            $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
            $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
            $productCollection->setVisibility($visibleCatalogIds)
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addAttributeToSelect("*")
                ->addStoreFilter()
                ->addAttributeToFilter(
                    "news_from_date",
                    [
                        "or"=>[
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
                    [
                        ["attribute"=>"news_from_date", "is"=>new \Zend_Db_Expr("not null")],
                        ["attribute"=>"news_to_date", "is"=>new \Zend_Db_Expr("not null")]
                    ]
                );
            $this->returnArray["totalCount"] = $customFilterData;
            if ($this->pageNumber >= 1) {
                $productCollection->setPageSize($customFilterData)->setCurPage($this->pageNumber);
            }
        }
        $this->collection = $productCollection;
    }

    /**
     * Function to get layered data
     *
     * @return object
     */
    protected function getLayeredData()
    {
        $layeredData = [];
        $doCategory = true;
        if (count($this->filterData) > 0) {
            if (in_array("cat", $this->filterData[1])) {
                $doCategory = false;
            }
        }
        if ($this->type == "category" && $doCategory) {
            $categoryFilterModel = $this->categoryLayer;
            if ($categoryFilterModel->getItemsCount()) {
                $each = [];
                $each["code"] = "cat";
                $each["label"] = $categoryFilterModel->getName();
                $each["options"] = $this->addCountToCategories($this->loadedCategory->getChildrenCategories());
                if (!empty($each["options"])) {
                    $layeredData[] = $each;
                }
            }
        }
        $doPrice = true;
        if (count($this->filterData) > 0) {
            if (in_array("price", $this->filterData[1])) {
                $doPrice = false;
            }
        }
        $filters = $this->filterableAttributes->getList();
        if ($this->type == "notification") {
            $layeredData = $this->getNotificationLayerData();
            $this->returnArray["layeredData"] = $layeredData;
            return;
        }
        if ($this->type == "customCollection") {
            $layeredData = $this->getCustomCollectionLayerData();
            $this->returnArray["layeredData"] = $layeredData;
            return;
        }
        if ($this->type == "advSearch") {
            $layeredData = $this->advSearchLayeredData();
            $this->returnArray["layeredData"] = $layeredData;
        }
        if ($this->type != "custom" && $this->type != "customCarousel" && $this->type != "advSearch") {
            $layeredData = $this->getCustomLayeredData($filters, $doPrice, $layeredData);
        }
        $this->returnArray["layeredData"] = $layeredData;
    }

    public function getNotificationLayerData()
    {
        $layeredData = [];
        $filters = $this->filterableAttributes->getList();
        foreach ($filters as $filter) {
            $doAttribute = true;
            if (count($this->filterData) > 0) {
                if (in_array($filter->getAttributeCode(), $this->filterData[1])) {
                    $doAttribute = false;
                }
            }
            if ($doAttribute) {
                $attributeFilterModel = $this->filterAttribute->setAttributeModel($filter);
                if ($attributeFilterModel->getItemsCount()) {
                    $each = [];
                    $each["code"] = $filter->getAttributeCode();
                    $each["label"] = $filter->getFrontendLabel();
                    $each["options"] = $this->helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                    $layeredData[] = $each;
                }
            }
        }
        return $layeredData;
    }

    public function getCustomCollectionLayerData()
    {
        $doPrice = true;
        $layeredData = [];
        if (count($this->filterData) > 0) {
            if (in_array("price", $this->filterData[1])) {
                $doPrice = false;
            }
        }
        $filters = $this->filterableAttributes->getList();
        foreach ($filters as $filter) {
            if ($filter->getFrontendInput() == "price") {
                if ($doPrice) {
                    $priceFilterModel = $this->filterPriceDataprovider->create();
                    if ($priceFilterModel) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getStoreLabel();
                        $each["options"] = $this->helperCatalog->getPriceFilter($priceFilterModel, $this->storeId);
                        if (!empty($each["options"])) {
                            $layeredData[] = $each;
                        }
                    }
                }
            } else {
                $doAttribute = true;
                if (count($this->filterData) > 0) {
                    if (in_array($filter->getAttributeCode(), $this->filterData[1])) {
                        $doAttribute = false;
                    }
                }
                if ($doAttribute) {
                    $attributeFilterModel = $this->layerAttribute->create()->setAttributeModel($filter);
                    if ($attributeFilterModel->getItemsCount()) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getStoreLabel();
                        $each["options"] = $this->helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                        if (!empty($each["options"])) {
                            $layeredData[] = $each;
                        }
                    }
                }
            }
        }
        return $layeredData;
    }

    public function advSearchLayeredData()
    {
        $this->mobikulLayer->customCollection = $this->collection;
        $this->mobikulLayerPrice->customCollection = $this->collection;
        $layeredData = [];
        $doPrice = true;
        if (count($this->filterData) > 0) {
            if (in_array("price", $this->filterData[1])) {
                $doPrice = false;
            }
        }
        $filters = $this->filterableAttributes->getList();
        foreach ($filters as $filter) {
            if ($filter->getFrontendInput() == "price") {
                if ($doPrice) {
                    $priceFilterModel = $this->filterPriceDataprovider->create();
                    if ($priceFilterModel) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getStoreLabel();
                        $each["options"] = $this->helperCatalog->getPriceFilter($priceFilterModel, $this->storeId);
                        if (!empty($each["options"])) {
                            $layeredData[] = $each;
                        }
                    }
                }
            } else {
                $doAttribute = true;
                if (!empty($this->filterData)) {
                    if (in_array($filter->getAttributeCode(), $this->filterData[1])) {
                        $doAttribute = false;
                    }
                }
                if ($doAttribute) {
                    $attributeFilterModel = $this->layerAttribute->create()->setAttributeModel($filter);
                    if ($attributeFilterModel->getItemsCount()) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getStoreLabel();
                        $each["options"] = $this->helperCatalog->getAttributeFilter($attributeFilterModel, $filter);
                        if (!empty($each["options"])) {
                            $layeredData[] = $each;
                        }
                    }
                }
            }
        }
        return $layeredData;
    }

    protected function getCustomLayeredData($filters, $doPrice, $layeredData)
    {
        foreach ($filters as $filter) {
            if ($filter->getFrontendInput() == "price") {
                if ($doPrice) {
                    $priceFilterModel = $this->filterPriceDataprovider->create();
                    if ($priceFilterModel) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getStoreLabel();
                        $each["options"] = $this->helperCatalog->getPriceFilterOptions($filter, $this->collection);
                        if (!empty($each["options"])) {
                            $layeredData[] = $each;
                        }
                    }
                }
            } else {
                $doAttribute = true;
                if (count($this->filterData) > 0) {
                    if (in_array($filter->getAttributeCode(), $this->filterData[1])) {
                        $doAttribute = false;
                    }
                }
                if ($doAttribute) {
                    $attributeFilterModel = $this->layerAttribute->create()->setAttributeModel($filter);
                    if ($attributeFilterModel->getItemsCount()) {
                        $each = [];
                        $each["code"] = $filter->getAttributeCode();
                        $each["label"] = $filter->getStoreLabel();
                        $each["options"] = $this->helperCatalog->getFilterOptions($filter, $this->collection);
                        if (!empty($each["options"])) {
                            $layeredData[] = $each;
                        }
                    }
                }
            }
        }
        return $layeredData;
    }

    /**
     * Function to get sorting data
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
        $this->returnArray["sortingData"] = $sortingData;
    }

    /**
     * Function to sort production Collection
     *
     * @return void
     */
    protected function sortProductCollection()
    {
        //Product flat enabled return true/false.
        $isFlatEnabled = $this->collection->isEnabledFlat();
        $this->collection->addAttributeToSelect(['TRIM(name)','*']);

        if (count($this->sortData) > 0) {
            $sortBy = $this->sortData[0];
            if ($this->sortData[1] == 0) {
                if ($isFlatEnabled) {
                    $this->collection->setOrder($sortBy, "ASC");
                } else {
                    $this->collection->addAttributeToSort($sortBy, "ASC");
                }
            } else {
                if ($isFlatEnabled) {
                    $this->collection->setOrder($sortBy, "DESC");
                } else {
                    $this->collection->addAttributeToSort($sortBy, "DESC");
                }
            }
        } else {
            if ($this->collection) {
                if ($isFlatEnabled) {
                    $this->collection->setOrder("position", "ASC");
                } else {
                    $this->collection->addAttributeToSort("position", "ASC");
                }
            }
        }
    }

    /**
     * Function to set category Images in the return array
     *
     * @return void
     */
    protected function getCategoryImages()
    {
        $categoryImageCollection = $this->categoryImageFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter("category_id", $this->id)
            ->addFieldToFilter([
                'store_id',
                'store_id'
            ], [
                ["finset" => 0],
                ["finset" => $this->storeId]
            ]);
        $bannerWidth = $this->helper->getValidDimensions($this->mFactor, $this->width);
        $bannerHeight = $this->helper->getValidDimensions($this->mFactor, 2*($this->width/3));
        foreach ($categoryImageCollection as $categoryImage) {
            $bannerArray = explode(",", $categoryImage->getBanner());
            if (!empty($bannerArray)) {
                foreach ($bannerArray as $banner) {
                    $basePath = $this->baseDir.DS."mobikul".DS."categoryimages".DS."banner".DS.$banner;
                    $newUrl = "";
                    $dominantColorPath = "";
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir.DS."mobikulresized".DS.$bannerWidth."x".
                            $bannerHeight.DS."categoryimages".DS."banner".DS.$banner;
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $bannerWidth, $bannerHeight);
                        $newUrl = $this->helper->getUrl("media")."mobikulresized".DS.$bannerWidth."x".
                            $bannerHeight.DS."categoryimages".DS."banner".DS.$banner;
                        $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.$bannerWidth."x".
                            $bannerHeight.DS."categoryimages".DS."banner".DS.$banner;
                    }
                    $bannerData = [];
                    $bannerData["bannerImage"] = $newUrl;
                    $bannerData["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                    $this->returnArray['banners'][] = $bannerData;
                }
            }
        }
    }

    /**
     * Fucntion to add Count to categories
     *
     * @param object $categoryCollection categoryCollection
     *
     * @return array
     */
    public function addCountToCategories($categoryCollection)
    {
        $isAnchor = [];
        $isNotAnchor = [];
        foreach ($categoryCollection as $category) {
            if ($category->getIsAnchor()) {
                $isAnchor[] = $category->getId();
            } else {
                $isNotAnchor[] = $category->getId();
            }
        }
        $productCounts = [];
        if ($isAnchor || $isNotAnchor) {
            $select = $this->getProductCountSelect();
            $this->eventManager->dispatch(
                "catalog_product_collection_before_add_count_to_categories",
                ["collection" => $this->collection]
            );
            if ($isAnchor) {
                $anchorStmt = clone $select;
                $anchorStmt->limit();
                $anchorStmt->where("count_table.category_id IN (?)", $isAnchor);
                $productCounts += $this->collection->getConnection()->fetchPairs($anchorStmt);
                $anchorStmt = null;
            }
            if ($isNotAnchor) {
                $notAnchorStmt = clone $select;
                $notAnchorStmt->limit();
                $notAnchorStmt->where("count_table.category_id IN (?)", $isNotAnchor);
                $notAnchorStmt->where("count_table.is_parent = 1");
                $productCounts += $this->collection->getConnection()->fetchPairs($notAnchorStmt);
                $notAnchorStmt = null;
            }
            $select = null;
            $this->productCountSelect = null;
        }
        $data = [];
        foreach ($categoryCollection as $category) {
            $_count = 0;
            if (isset($productCounts[$category->getId()])) {
                $_count = $productCounts[$category->getId()];
            }
            if ($category->getIsActive() && $_count > 0) {
                $data[] = [
                    "id" => $category->getId(),
                    "label" => htmlspecialchars_decode($this->helperCatalog->stripTags($category->getName())),
                    "count" => $_count
                ];
            }
        }
        return $data;
    }

    /**
     * Function to get selected product Count
     *
     * @return integer
     */
    public function getProductCountSelect()
    {
        $this->productCountSelect = clone $this->collection->getSelect();
        $this->productCountSelect->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->reset(\Magento\Framework\DB\Select::GROUP)
            ->reset(\Magento\Framework\DB\Select::ORDER)
            ->distinct(false)
            ->join(
                [
                    "count_table" => $this->collection->getTable("catalog_category_product_index")
                ],
                "count_table.product_id = e.entity_id",
                [
                    "count_table.category_id",
                    "product_count" => new \Zend_Db_Expr("COUNT(DISTINCT count_table.product_id)")
                ]
            )
            ->where("count_table.store_id = ?", $this->storeId)
            ->group("count_table.category_id");
        return $this->productCountSelect;
    }

    protected function getSearchCriterias($searchCriterias)
    {
        $middle = ceil(count($searchCriterias) / 2);
        $left = array_slice($searchCriterias, 0, $middle);
        $right = array_slice($searchCriterias, $middle);
        return ["left"=>$left, "right"=>$right];
    }
}
