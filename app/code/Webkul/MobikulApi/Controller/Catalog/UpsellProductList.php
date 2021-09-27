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

namespace Webkul\MobikulApi\Controller\Catalog;

/**
 * Class UpsellProductList api
 */
class UpsellProductList extends AbstractCatalog
{
    /**
     * Execute function for Class UpsellProductList
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if ($this->productId > 0) {
                $this->product = $this->productFactory->create()->load($this->productId);
                $pageSize = $this->helperCatalog->getPageSize();
                $upsellProductCollection = $this->product->getUpSellProductCollection()
                    ->setPositionOrder()->addStoreFilter();
                $upsellProductCollection->setVisibility($this->productVisibility->getVisibleInSiteIds());
                $upsellProductList = [];
                $upsellProductCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
                foreach ($upsellProductCollection as $eachProduct) {
                    $upsellProductList[] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $this->storeId,
                        $this->width,
                        $this->customerId
                    );
                }
                $this->returnArray["productList"] = $upsellProductList;
            } else {
                $this->returnArray["message"] = __("Invalid Product Id");
                return $this->getJsonResponse($this->returnArray);
            }
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["success"] = true;
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
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
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->url = $this->wholeData["url"] ?? "";
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->websiteId = $this->wholeData["websiteId"] ?? 0;
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = __("Customer does Not Exists");
                $this->customerId = 0;
                return $this->getJsonResponse($this->returnArray);
            } elseif ($this->customerId != 0) {
                $this->customer = $this->customerFactory->create()->load($this->customerId);
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
