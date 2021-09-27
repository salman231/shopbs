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

namespace Webkul\MobikulApi\Controller\Customer;

class ReviewList extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "REVIEWLIST".$this->storeId.$this->customerToken.$this->width.$this->pageNumber;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $reviewCollection = $this->reviewCollection
                ->create()
                ->setDateOrder()
                ->addStoreFilter($this->storeId);

            $reviewCollection = $reviewCollection->join(
                    ['detail' => $reviewCollection->getTable('review_detail')],
                    'main_table.review_id = detail.review_id',
                    ['detail_id', 'title', 'detail', 'nickname', 'customer_id']
                )
                ->addCustomerFilter($this->customerId);
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $reviewCollection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $reviewCollection->setPageSize($pageSize)
                    ->setCurPage($this->pageNumber);
            }
            // applying pagination for dashboard ////////////////////////////////////
            if ($this->forDashboard) {
                $reviewCollection->setPageSize(5)->setCurPage($this->pageNumber);
            }
            // Creating Review List /////////////////////////////////////////////////
            $reviewList = [];
            foreach ($reviewCollection as $key => $review) {
                $product = $this->productFactory->create()->load($review->getEntityPkValue());
                $eachReview = [];
                $eachReview["id"] = $key;
                $eachReview["productId"] = (int)$product->getId();
                $eachReview["thumbNail"] = $this->helperCatalog->getImageUrl($product, $this->width/3);
                $eachReview["productName"] = $this->helperCatalog->stripTags($product->getName());
                $eachReview["dominantColor"] = $this->helper->getDominantColor(
                    $this->helper->getDominantColorFilePath($eachReview["thumbNail"])
                );
                $ratingCollection = $this->vote
                    ->getResourceCollection()
                    ->setReviewFilter($review->getReviewId())
                    ->addRatingInfo($this->storeId)
                    ->setStoreFilter($this->storeId)
                    ->load();
                $totalRating = 0;
                $ratingCount = 0;
                foreach ($ratingCollection as $rating) {
                    $totalRating += $rating->getPercent();
                    $ratingCount++;
                }
                if ($ratingCount == 0) {
                    $eachReview["customerRating"] = 0;
                } else {
                    $eachReview["customerRating"] = (int)($totalRating/$ratingCount)/20;
                }
                $reviewList[] = $eachReview;
            }
            $this->returnArray["reviewList"] = $reviewList;
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            if (hash("sha256", $encodedData) == $this->eTag) {
                $this->returnArray["statusCode"] = 304;
                $cacheStatus = (bool)$this->helper->getConfigData("mobikul/cachesettings/enable");
                if ($cacheStatus) {
                    $counter = $this->helper->getConfigData("mobikul/cachesettings/counter");
                    if ($counter == "") {
                        $counter = 5;
                    }
                    return $this->getJsonResponse($this->returnArray, 304);
                }
            }
            $this->helper->updateCache($cacheString, $encodedData);
            $this->returnArray["eTag"] = hash("sha256", $encodedData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify the request
     *
     * @return void|jSon
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET") {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? false;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
