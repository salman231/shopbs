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

class SaveReview extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "As customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            }
            if ($this->customerId == 0) {
                $this->customerId = null;
            }
            $review = $this->review
                ->setEntityPkValue($this->productId)
                ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
                ->setTitle($this->title)
                ->setDetail($this->detail)
                ->setEntityId(1)
                ->setStoreId($this->storeId);
            if ($this->customerId != 0) {
                $review->setCustomerId($this->customerId);
            }
            $review->setNickname($this->nickname)
                ->setReviewId($review->getId())
                ->setStores([$this->storeId])
                ->save();
            foreach ($this->ratings as $ratingId => $optionId) {
                $this->ratingFactory->create()
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getId())
                    ->setCustomerId($this->customerId)
                    ->addOptionVote($optionId, $this->productId);
            }
            $review->aggregate();
            $this->returnArray["message"] = __("Your review has been accepted for moderation.");
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
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
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->title = $this->wholeData["title"] ?? "";
            $this->detail = $this->wholeData["detail"] ?? "";
            $this->ratings = $this->wholeData["ratings"] ?? "[]";
            $this->ratings = $this->jsonHelper->jsonDecode($this->ratings);
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->nickname = $this->wholeData["nickname"] ?? "";
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
