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
 * Class Category Page Data api
 */
class CategoryPageData extends AbstractCatalog
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
            $cacheString = "CATEGORYPAGEDATA".$this->width.$this->storeId.$this->categoryId.
            $this->quoteId.$this->mFactor.$this->customerToken.$this->currency;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Setting currency /////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($this->currency);
            // Child categories /////////////////////////////////////////////////////
            $catCollection = $this->categoryCollectionFactory->create()
                ->addAttributeToSelect("*")
                ->addFieldToFilter("parent_id", $this->categoryId)
                ->addFieldToFilter("is_active", 1)
                ->addFieldToFilter("include_in_menu", 1);
            $categories = [];
            foreach ($catCollection as $cc) {
                $categories[] = [
                    "id" => $cc->getEntityId(),
                    "name" => $cc->getName(),
                    "hasChildren" => $cc->getChildrenCount() > 0 ? true : false
                ];
            }
            $this->returnArray['categories'] = $categories;
            // Creating product collection //////////////////////////////////////////
            $this->loadedCategory = $this->category->create()->setStoreId($this->storeId)->load($this->categoryId);
            
            $categoryToFilter = $this->category->create()->load($this->categoryId);
            
            $this->coreRegistry->register("current_category", $this->loadedCategory);
            $this->collection = $this->helperCatalog->getProductListColl($this->categoryId);
            $this->collection->setStoreId($this->storeId)->addCategoryFilter($categoryToFilter);
            $this->collection->addAttributeToSelect("*");
            if ($this->collection && $this->helperCatalog->showOutOfStock() == 0) {
                $this->stockFilter->addInStockFilterToCollection($this->collection);
            }
            $this->collection->setPageSize(5)->setCurPage(1);

            // Creating product collection //////////////////////////////////////////
            $productList = [];
            if ($this->collection) {
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
            // Hot seller List //////////////////////////////////////////////////////
            $collection = $this->productList->getData("conditions");
            $this->getHotSellerCollection();
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
            $this->returnArray["bannerImage"] = $this->getCategoryImages();
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
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->mFactor = $this->helper->calcMFactor($this->mFactor);
            $this->categoryId = $this->wholeData["categoryId"] ?? 0;
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
     * Function to set category Images in the return array
     *
     * @return array
     */
    protected function getCategoryImages()
    {
        $bannerImages = [];
        $categoryImageCollection = $this->categoryImageFactory->create()->getCollection()
            ->addFieldToFilter("category_id", $this->categoryId)
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
            if ($categoryImage->getBanner() != "") {
                foreach (explode(",", $categoryImage->getBanner()) as $banner) {
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
                    $bannerImages[] = [
                        "url" => $newUrl,
                        "dominantColor" => $this->helper->getDominantColor($dominantColorPath)
                    ];
                }
            }
        }
        return $bannerImages;
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
                ["count_table" => $this->collection->getTable("catalog_category_product_index")],
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

    /**
     * Function to get Best Seller Collection
     *
     * @return array
     */
    public function getHotSellerCollection()
    {
        $productCollection = $this->reportCollectionFactory;
        $collection = $this->bestSellerCollection;
        $bestSalesProductIds = [];
        foreach ($collection as $product) {
            $bestSalesProductIds[] = $product->getProductId();
        }
        $productCollection = $this->productCollection->create()
            ->addFieldToFilter("entity_id", ["in" => $bestSalesProductIds])
            ->addFieldToSelect("*");
        if ($this->categoryId > 0) {
            $productCollection->addCategoriesFilter(["eq" => $this->categoryId]);
        }
        $hotSellerList = [];
        foreach ($productCollection as $eachProduct) {
            $hotSellerList[] = $this->helperCatalog->getOneProductRelevantData(
                $eachProduct,
                $this->storeId,
                $this->width,
                $this->customerId
            );
        }
        $this->returnArray["hotSeller"] = $hotSellerList;
    }
}
