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

class CustomCollection extends AbstractMobikul
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $productCollection = $this->productCollection;
            $notification = $this->mobikulNotification->create()->load($this->notificationId);
            $customFilterData = $this->serializer->unserialize($notification->getFilterData());
            $notificationCollectionType = $notification->getCollectionType();
            $productCollection = $this->getProductCollection($notificationCollectionType, $customFilterData);
            // Filtering product collection /////////////////////////////////////////
            $productCollection = $this->getFilteredProductCollection($productCollection, $this->filterData);
            // Applying pagination //////////////////////////////////////////////////
            if ($notification->getCollectionType() != "product_new" && $this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $productCollection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $productCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            // Sorting product collection ///////////////////////////////////////////
            if (count($this->sortData) > 0) {
                $sortBy = $this->sortData[0];
                if ($this->sortData[1] == 0) {
                    $productCollection->setOrder($sortBy, "ASC");
                } else {
                    $productCollection->setOrder($sortBy, "DESC");
                }
            } elseif ($notification->getCollectionType() == "product_new") {
                $productCollection->addAttributeToSort("news_from_date", "DESC");
            }
            foreach ($productCollection as $eachProduct) {
                $eachProduct = $this->productFactory->create()->load($eachProduct->getId());
                if ($eachProduct->isAvailable() ||
                    (bool)$this->helper->getConfigData("cataloginventory/options/show_out_of_stock")
                ) {
                    $this->returnArray["productList"][] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $this->storeId,
                        $this->width,
                        $this->customerId
                    );
                }
            }
            // Creating layered attribute collection ////////////////////////////////
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
            $this->returnArray["layeredData"] = $layeredData;
            // Creating sort attribute collection ///////////////////////////////////
            $sortingData = [];
            $toolbar = $this->toolbar;
            foreach ($toolbar->getAvailableOrders() as $key => $order) {
                $each = [];
                $each["code"] = $key;
                $each["label"] = $order;
                $sortingData[] = $each;
            }
            $this->returnArray["sortingData"] = $sortingData;
            if ($this->customerId != 0) {
                $quote = $this->quote->getCollection()
                    ->addFieldToFilter("customer_id", $this->customerId)
                    ->addFieldToFilter("is_active", 1)
                    ->addOrder("updated_at", "DESC")
                    ->getFirstItem();
                $quote->collectTotals()->save();
                $this->returnArray["cartCount"] = $quote->getItemsQty() * 1;
            }
            if ($this->quoteId != 0) {
                $this->returnArray["cartCount"] = $this->quote->setStoreId($this->storeId)->load($this->quoteId)
                    ->getItemsQty() * 1;
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function getProductCollection($type, $customFilterData)
    {
        $productCollection = $this->productCollection;
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
        return $productCollection;
    }

    public function getFilteredProductCollection($productCollection, $filterData)
    {
        if (count($filterData) > 0) {
            $filterCount = count($filterData[0]);
            for ($i=0; $i<$filterCount; ++$i) {
                if ($filterData[0][$i] != "" && $filterData[1][$i] == "price") {
                    $minPossiblePrice = .01;
                    $currencyRate = $productCollection->getCurrencyRate();
                    $priceRange = explode("-", $filterData[0][$i]);
                    $from = $priceRange[0];
                    $to = $priceRange[1];
                    $fromRange = ($from - ($minPossiblePrice / 2)) / $currencyRate;
                    $toRange = ($to - ($minPossiblePrice / 2)) / $currencyRate;
                    $select = $productCollection->getSelect();
                    if ($from !== "") {
                        $select->where("price_index.min_price".">=".$fromRange);
                    }
                    if ($to !== "") {
                        $select->where("price_index.min_price"."<".$toRange);
                    }
                } elseif ($filterData[0][$i] != "" && $filterData[1][$i] == "cat") {
                    $categoryToFilter = $this->categoryFactory->create()->load($filterData[0][$i]);
                    $productCollection->setStoreId($this->storeId)->addCategoryFilter($categoryToFilter);
                } else {
                    $attribute = $this->eavConfig->getAttribute("catalog_product", $filterData[1][$i]);
                    $this->filterAttribute->setAttributeModel($attribute);
                    $filterAtr = $this->layerFilterAttribute;
                    $connection = $filterAtr->getConnection();
                    $tableAlias = $attribute->getAttributeCode()."_idx";
                    $conditions = [
                        "{$tableAlias}.entity_id = e.entity_id",
                        $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
                        $connection->quoteInto("{$tableAlias}.store_id = ?", $productCollection->getStoreId()),
                        $connection->quoteInto("{$tableAlias}.value = ?", $filterData[0][$i]),
                    ];
                    $productCollection->getSelect()->join(
                        [$tableAlias=>$filterAtr->getMainTable()],
                        implode(" AND ", $conditions),
                        []
                    );
                }
            }
        }
        return $productCollection;
    }

    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->sortData = $this->wholeData["sortData"] ?? "[]";
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->filterData = $this->wholeData["filterData"] ?? "[]";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->notificationId = $this->wholeData["notificationId"] ?? 0;
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            $this->sortData = $this->jsonHelper->jsonDecode($this->sortData);
            $this->filterData = $this->jsonHelper->jsonDecode($this->filterData);
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
}
