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
 * Class GetCategoryList
 * To provide list of categories
 */
class GetCategoryList extends AbstractCatalog
{
    /**
     * Execite Funciton for class GetCategoryList
     *
     * @return json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "GETCATEGORYLIST".$this->storeId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Getting category banner image ////////////////////////////////////////
            $categoryCollection = $this->getCategoryCollection(true, 2, false, false);
            $categoryData = [];
            foreach ($categoryCollection as $category) {
                $categoryData[$category->getId()]["id"] = $category->getId();
                $categoryData[$category->getId()]["name"] = $category->getName();
                if ($category->getImageUrl() != "") {
                    $categoryData[$category->getId()]["img_url"] = $category->getImageUrl();
                    $categoryData[$category->getId()]["dominantColor"] = $this->helper->getDominantColor(
                        $this->helper->getDominantColorFilePath($category->getImageUrl())
                    );
                } else {
                    $categoryData[$category->getId()]["img_url"] = "";
                    $categoryData[$category->getId()]["dominantColor"] = "";
                }
            }
            $this->returnArray["categoryData"] = $categoryData;
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
     * Get catgeory collection with parameters
     *
     * @param bool    $isActive isActive
     * @param bool    $level    level
     * @param bool    $sortBy   sortby
     * @param integer $pageSize pageSize
     *
     * @return collection
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->categoryCollectionFactory->create()
            ->addFieldToFilter("entity_id", ["neq"=>2]);
        $collection->addAttributeToSelect("*");
        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }
        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }
        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }
        return $collection;
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
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
