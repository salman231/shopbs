<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Api\Customer;

use Magento\Framework\Exception\LocalizedException;
use Webkul\DeliveryBoy\Helper\ModuleGlobalConstants;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;

class AddReview extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if ($this->title == "") {
                throw new LocalizedException(__("Title is required field."));
            }
            if ($this->rating > 5.0 || $this->rating < 0.5) {
                throw new LocalizedException(__("Invalid Rating."));
            }
            if ($this->deliveryboyId == 0 || !is_numeric($this->deliveryboyId)) {
                throw new LocalizedException(__("Invalid Deliveryboy."));
            }
            $review = $this->ratingFactory->create()
                ->setTitle($this->title)
                ->setComment($this->comment)
                ->setDeliveryboyId($this->deliveryboyId)
                ->setCustomerId($this->customerId)
                ->setRating($this->rating)
                ->setStatus(2)
                ->setCreatedAt($this->date->gmtDate())
                ->save();
            $deliveryboy = $this->deliveryboyResourceCollection->create()->addFieldToFilter(
                DeliveryboyInterface::ID,
                $this->deliveryboyId
            )->getFirstItem();
            $customer = $this->customerFactory->create()->load($this->customerId);
            $storeId = $customer->getStoreId();
            $customerName = $customer->getName() ?? __("Guest");
            $ratingMaxLimit = ModuleGlobalConstants::RATING_MAX_VALUE;
            $ratingManagerName = ModuleGlobalConstants::DEFAULT_RATING_MANAGER_NAME;
            $senderInfo = [
                'name' => ModuleGlobalConstants::DEFAULT_ADMIN_NAME,
                'email' => $this->deliveryboyHelper->getConfigData(
                    ModuleGlobalConstants::DEFAULT_ADMIN_EMAIL_XML_PATH
                ),
            ];
            $receiversInfo = [
                'name' => $deliveryboy->getName(),
                'email' => $deliveryboy->getEmail(),
            ];
            $ratingStatusLabel = $this->deliveryboyHelper->getRatingStatuses()[$review->getStatus()];
            $review->setStatus($ratingStatusLabel);
            $this->_eventManager->dispatch(
                'inform_deliveryboy_new_review_event',
                [
                    'store_id' => $storeId,
                    'customer_name' => $customerName,
                    'rating_max_limit' => $ratingMaxLimit,
                    'rating_manager_name' => $ratingManagerName,
                    'deliveryboy' => $deliveryboy,
                    'sender_info' => $senderInfo,
                    'receivers_info' => $receiversInfo,
                    'review' => $review,
                ]
            );
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Thanks for your review, it is submitted for moderation.");
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->title = trim($this->wholeData["title"] ?? "");
            $this->rating = trim($this->wholeData["rating"] ?? 0);
            $this->comment = trim($this->wholeData["comment"] ?? "");
            $this->customerId = trim($this->wholeData["customerId"] ?? 0);
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }
}
