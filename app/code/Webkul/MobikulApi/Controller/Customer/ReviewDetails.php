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

class ReviewDetails extends AbstractCustomer
{

    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "REVIEWDETAILS".$this->width.$this->storeId.$this->reviewId.$this->customerToken;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $review = $this->review->load($this->reviewId);
            if ($review->getCustomerId() != $this->customerId) {
                $this->returnArray["message"] = __("Invalid review.");
                return $this->getJsonResponse($this->returnArray);
            }
            $product = $this->productFactory->create()
                ->setStoreId($this->storeId)
                ->load($review->getEntityPkValue());
            $this->returnArray["thumbNail"] = $this->helperCatalog->getImageUrl($product, $this->width/2);
            $this->returnArray["productId"] = (int)$product->getId();
            $this->returnArray["productName"] = htmlspecialchars_decode(
                $this->helperCatalog->stripTags($product->getName())
            );
            $this->returnArray["dominantColor"] = $this->helper->getDominantColor(
                $this->helper->getDominantColorFilePath($this->returnArray["thumbNail"])
            );
            $ratingArray = [];
            $ratingCollection = $this->vote
                ->getResourceCollection()
                ->setReviewFilter($this->reviewId)
                ->addRatingInfo($this->storeId)
                ->setStoreFilter($this->storeId)
                ->load();
            foreach ($ratingCollection as $rating) {
                $eachRating = [];
                $eachRating["ratingCode"] = $this->helperCatalog->stripTags($rating->getRatingCode());
                $eachRating["ratingValue"] = (double)number_format($rating->getPercent(), 2, ".", "");
                $ratingArray[] = $eachRating;
            }
            $this->returnArray["ratingData"] = $ratingArray;
            $this->returnArray["reviewDate"] = __("Your Review (submitted on ").$this->helperCatalog->formatDate(
                $review->getCreatedAt(),
                "long"
            ).")";
            $this->returnArray["reviewTitle"] = htmlspecialchars_decode(
                $this->helperCatalog->stripTags($review->getTitle())
            );
            $this->returnArray["reviewDetail"] = htmlspecialchars_decode(
                $this->helperCatalog->stripTags($review->getDetail())
            );
            $reviews = $this->review
                ->getResourceCollection()
                ->addStoreFilter($this->storeId)
                ->addEntityFilter("product", $product->getId())
                ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->setDateOrder()
                ->addRateVotes();
            $ratings = [];
            $totalRatingCount = $reviews->getSize();
            if ($totalRatingCount > 0) {
                foreach ($reviews->getItems() as $review) {
                    foreach ($review->getRatingVotes() as $vote) {
                        $ratings[] = $vote->getPercent();
                    }
                }
            }
            if (count($ratings) > 0) {
                $this->returnArray["averageRating"] = (double)number_format(
                    (5*(array_sum($ratings) / count($ratings))
                    )/100,
                    2,
                    ".",
                    ""
                );
            } else {
                $this->returnArray["averageRating"] = 0.0;
            }
            $this->returnArray["success"] = true;
            $this->returnArray["totalProductReviews"] = $totalRatingCount;
            $this->emulate->stopEnvironmentEmulation($environment);
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
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->reviewId = $this->wholeData["reviewId"] ?? 0;
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
