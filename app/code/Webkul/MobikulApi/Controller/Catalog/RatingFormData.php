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
 * Class ReviewsAndRatings
 */
class RatingFormData extends AbstractCatalog
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
            $ratingCollection = $this->rating->getResourceCollection()->addEntityFilter(
                'product'
            )->setPositionOrder()->addRatingPerStoreName(
                $this->storeId
            )->setStoreFilter(
                $this->storeId
            )->setActiveFilter(
                true
            )->load()->addOptionToItems();
            ;
            
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
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
