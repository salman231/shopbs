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
 * Class ReviewsAndRatings api
 */
class ReviewsAndRatings extends AbstractCatalog
{
    /**
     * Execute function for Class ReviewsAndRatings
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $ratingFormData = [];
            $ratingCollection = $this->rating
                ->getResourceCollection()
                ->addEntityFilter("product")
                ->setPositionOrder()
                ->setStoreFilter($this->storeId)
                ->addRatingPerStoreName($this->storeId)
                ->load()
                ->addOptionToItems();
            foreach ($ratingCollection as $rating) {
                $eachTypeRating = [];
                $eachRatingFormData = [];
                foreach ($rating->getOptions() as $option) {
                    $eachTypeRating[] = $option->getId();
                }
                $eachRatingFormData["id"] = $rating->getId();
                $eachRatingFormData["name"] = $this->helperCatalog->stripTags($rating->getRatingCode());
                $eachRatingFormData["values"] = $eachTypeRating;
                $ratingFormData[] = $eachRatingFormData;
            }
            $this->returnArray["ratingFormData"] = $ratingFormData;
            // Getting rating data //////////////////////////////////////////////////
            $ratingCollection->addEntitySummaryToItem($this->productId, $this->storeId);
            $ratingData = [];
            foreach ($ratingCollection as $rating) {
                if ($rating->getSummary()) {
                    $eachRating = [];
                    $eachRating["ratingCode"] = $this->helperCatalog->stripTags($rating->getRatingCode());
                    $eachRating["ratingValue"] = number_format((5 * $rating->getSummary()) / 100, 2, ".", "");
                    $ratingData[] = $eachRating;
                }
            }
            $this->returnArray["ratingData"] = $ratingData;
            // Getting review list //////////////////////////////////////////////////
            $reviewList = [];
            $ratingsArr = [];
            $reviewCollection = $this->review
                ->getResourceCollection()
                ->addStoreFilter($this->storeId)
                ->addEntityFilter("product", $this->productId)
                ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->setDateOrder()
                ->addRateVotes();
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $reviewCollection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $reviewCollection->setPageSize($pageSize)
                    ->setCurPage($this->pageNumber);
            }
            foreach ($reviewCollection as $review) {
                $oneReview = [];
                $ratings = [];
                $oneReview["title"] = $this->helperCatalog->stripTags($review->getTitle());
                $oneReview["details"] = $this->helperCatalog->stripTags($review->getDetail());
                $votes = $review->getRatingVotes();
                $totalRatings = 0;
                $totalRatingsCount = 0;
                if (count($votes)) {
                    foreach ($votes as $_vote) {
                        $oneVote = [];
                        $oneVote["label"] = $this->helperCatalog->stripTags($_vote->getRatingCode());
                        $oneVote["value"] = number_format($_vote->getValue(), 2, ".", "");
                        $totalRatings += number_format($_vote->getValue(), 2, ".", "");
                        $totalRatingsCount++;
                        $ratings[] = $oneVote;
                        $ratingsArr[] = $_vote->getPercent();
                    }
                }
                $oneReview["avgRatings"] = $totalRatingsCount ? round(
                    ($totalRatings/$totalRatingsCount),
                    2
                ) : $totalRatings;
                $oneReview["ratings"] = $ratings;
                $oneReview["reviewBy"] = __("Review by %1", $this->helperCatalog->stripTags($review->getNickname()));
                $oneReview["reviewOn"] = __(
                    "(Posted on %1)",
                    $this->helperCatalog->formatDate($review->getCreatedAt()),
                    "long"
                );
                $reviewList[] = $oneReview;
            }
            $this->returnArray["reviewList"] = $reviewList;
            $ratingVal = 0;
            if (count($ratingsArr) > 0) {
                $ratingVal = number_format((5 * (array_sum($ratingsArr) / count($ratingsArr))) / 100, 2, ".", "");
            }
            if (empty($ratingFormData)) {
                $this->returnArray["showRatings"] = false;
            } else {
                $this->returnArray["showRatings"] = true;
            }
            $this->returnArray["rating"] = $ratingVal;
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
