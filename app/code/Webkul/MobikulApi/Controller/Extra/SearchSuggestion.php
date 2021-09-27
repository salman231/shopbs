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

/**
 * Class search suggestion to get Search suggestions
 */
class SearchSuggestion extends AbstractMobikul
{
    /**
     * Execute function for class Search Suggetion
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $helper = $this->searchSuggestionHelper;
            $query = is_array($this->searchQuery) ? "" : trim($this->searchQuery);
            $maxQueryLength = $this->helper->getConfigData("catalog/search/max_query_length");
            $query = substr($this->searchQuery, 0, $maxQueryLength);
            $tagArray = [];
            $productArray = [];
            if ($helper->displayTags()) {
                $tagCollection = $this->queryCollection
                    ->addFieldToFilter("store_id", [["finset"=>[$this->storeId]]])
                    ->setPopularQueryFilter($this->storeId)
                    ->addFieldToFilter("query_text", ["like"=>"%".$query."%"])
                    ->setPageSize($helper->getNumberOfTags())
                    ->load()
                    ->getItems();
                foreach ($tagCollection as $item) {
                    $tagArray[] = [
                        "term" => $query,
                        "title" => $item->getQueryText(),
                        "count" => $item->getNumResults()
                    ];
                }
            }
            if ($helper->displayProducts()) {
                $productCollection = $this->productCollection;
                if ($this->categoryId > 0) {
                    $productCollection = $this->categoryFactory->create()->load($this->categoryId)
                        ->getProductCollection()
                        ->addAttributeToSelect("*")
                        ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
                        ->addAttributeToFilter("visibility", ["in"=>[2, 3, 4]])
                        ->addAttributeToFilter(
                            [
                                ["attribute"=>"sku", "like"=>"%".$query."%"],
                                ["attribute"=>"name", "like"=>"%".$query."%"],
                            ]
                        );
                } else {
                    $productCollection
                        ->addAttributeToSelect("*")
                        ->addAttributeToSelect("sku")
                        ->addAttributeToSelect("name")
                        ->addAttributeToSelect("description")
                        ->addAttributeToSelect("short_description")
                        ->addAttributeToFilter("status", ["in"=>$this->productStatus->getVisibleStatusIds()])
                        ->addAttributeToFilter(
                            [
                                ["attribute"=>"sku", "like"=>"%".$query."%"],
                                ["attribute"=>"name", "like"=>"%".$query."%"],
                            ]
                        )
                        ->addAttributeToFilter("visibility", ["in"=>[2, 3, 4]]);
                }
                $productCollection->setPageSize($helper->getNumberOfProducts());
                $productArray = $this->getProductArray($productCollection, $query);
            }
            $suggestData = [$tagArray, $productArray];
            $suggestProductArray = $this->getSuggestedProductArray($suggestData);
            $this->returnArray["success"] = true;
            $this->returnArray["suggestProductArray"] = $suggestProductArray;
            if (count($this->returnArray["suggestProductArray"]) == 0) {
                $this->returnArray["suggestProductArray"] = new \stdClass();
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to get Product Array
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection product collection
     * @param string                                                  $query             querys
     *
     * @return array
     */
    public function getProductArray($productCollection, $query)
    {
        $productArray = [];
        foreach ($productCollection as $item) {
            $price = number_format($item->getFinalPrice(), 2);
            $isSalePrice = $this->searchSuggestionHelper->isOnSale($item);
            $imgSrc = $this->imageHelper->init($item, "product_page_image_large")->resize(144, 144)->getUrl();
            if ($isSalePrice == true) {
                $specialPrice = number_format($item->getSpecialPrice(), 2);
            } else {
                $specialPrice = 0;
            }
            $bundleFromPrice = 0;
            $bundleToPrice = 0;
            
            if ($item->getTypeId() == "grouped") {
                $minPrice = 0;
                if ($item->getMinimalPrice() == "") {
                    $associatedProducts = $item->getTypeInstance(true)->getAssociatedProducts($item);
                    $minPriceArr = [];
                    foreach ($associatedProducts as $associatedProduct) {
                        if ($ogPrice = $associatedProduct->getPrice()) {
                            $minPriceArr[] = $ogPrice;
                        }
                    }
                    if (!empty($minPriceArr)) {
                        $minPrice = min($minPriceArr);
                    }
                    $price = $minPrice;
                } else {
                    $minPrice = $item->getMinimalPrice();
                    $price = $minPrice;
                }
            }
            if ($item->getTypeId() == "bundle") {
                $bundlePriceModel = $this->bundlePriceModel;
                $bundleFromPrice = $this->helperCatalog->stripTags(
                    $this->pricingHelper->currency($bundlePriceModel->getTotalPrices($item, "min", 1))
                );
                $bundleToPrice = $this->helperCatalog->stripTags(
                    $this->pricingHelper->currency($bundlePriceModel->getTotalPrices($item, "max", 1))
                );
                $price = $bundleFromPrice." - ".$bundleToPrice;
                $productArray[] = [
                    "term" => $query,
                    "type" => $item->getTypeId(),
                    "title" => $item->getName(),
                    "image" => $imgSrc,
                    "price" => $bundleFromPrice." - ".$bundleToPrice,
                    "productId" => $item->getId(),
                    "specialPrice" => $specialPrice
                ];
                continue;
            }

            if ($item->getTypeId() == "configurable") {
                $regularPrice = $item->getPriceInfo()->getPrice("regular_price");
                $price = $regularPrice->getAmount()->getBaseAmount();
                $specialPrice = $item->getSpecialPrice();
            } elseif (empty($price)) {
                $price = 0.0;
            }

            $productArray[] = [
                "term" => $query,
                "type" => $item->getTypeId(),
                "title" => $item->getName(),
                "image" => $imgSrc,
                "price" => $price,
                "productId" => $item->getId(),
                "specialPrice" => $specialPrice
            ];

        }
        return $productArray;
    }

    /**
     * Function to get suggested data
     *
     * @param array $suggestData suggest Data
     *
     * @return array
     */
    public function getSuggestedProductArray($suggestData)
    {
        $suggestProductArray = [];
        if (count($suggestData[0]) != 0 || count($suggestData[1]) != 0) {
            foreach ($suggestData[0] as $index => $item) {
                $eachSuggestion = [];
                $term = htmlspecialchars_decode($item["term"]);
                $title = htmlspecialchars_decode($item["title"]);
                $tagName = htmlspecialchars_decode($item["title"]);
                $len = strlen($term);
                $str = $this->searchSuggestionHelper->matchString($term, $tagName);
                $tagName = $this->searchSuggestionHelper->getBoldName($tagName, $str, $term);
                $eachSuggestion["label"] = $tagName;
                $eachSuggestion["count"] = $item["count"];
                $suggestProductArray["tags"][] = $eachSuggestion;
            }
            if (count($suggestData[1]) > 0) {
                foreach ($suggestData[1] as $index => $item) {
                    $eachSuggestion = [];
                    $term = htmlspecialchars_decode($item["term"]);
                    if ($item["type"] != 'bundle') {
                        $formattedPrice = strip_tags($this->cataloghelper->formatPrice($item["price"]));
                    } else {
                        $formattedPrice = $item["price"];
                    }
                    $imgUrl = htmlspecialchars_decode($item["image"]);
                    $productName = htmlspecialchars_decode($item["title"]);
                    $specialPrice = htmlspecialchars_decode($item["specialPrice"]);
                    $title = htmlspecialchars_decode($item["title"]);
                    $str = $this->searchSuggestionHelper->matchString($term, $productName);
                    $productName = $this->searchSuggestionHelper->getBoldName($productName, $str, $term);
                    $eachSuggestion["price"] = $formattedPrice;
                    $eachSuggestion["thumbNail"] = $imgUrl;
                    $eachSuggestion["productId"] = $item["productId"];
                    $eachSuggestion["productName"] = $productName;
                    $eachSuggestion["specialPrice"] = strip_tags($this->cataloghelper->formatPrice(0));
                    $eachSuggestion["hasSpecialPrice"] = false;
                    if ($specialPrice > 0) {
                        $eachSuggestion["hasSpecialPrice"] = true;
                        $eachSuggestion["specialPrice"] = strip_tags($this->cataloghelper->formatPrice($specialPrice));
                    }
                    $suggestProductArray["products"][] = $eachSuggestion;
                }
            }
        }
        return $suggestProductArray;
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->categoryId = $this->wholeData["categoryId"] ?? 0;
            $this->searchQuery = $this->wholeData["searchQuery"] ?? "";
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
