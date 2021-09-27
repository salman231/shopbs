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

namespace Webkul\DeliveryBoy\Controller\Adminhtml\Rating;

use Webkul\DeliveryBoy\Api\Data\RatingInterface;
use Webkul\DeliveryBoy\Helper\ModuleGlobalConstants;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;
use Webkul\DeliveryBoy\Model\Rating;

class MassDisapprove extends \Webkul\DeliveryBoy\Controller\Adminhtml\Rating
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $ratingUpdated = 0;
        $coditionArr = [];
        foreach ($collection->getAllIds() as $ratingId) {
            $currentRating = $this->ratingRepository->getById($ratingId);
            $ratingData = $currentRating->getData();
            if (count($ratingData)) {
                $condition = "`id`=" . $ratingId;
                array_push($coditionArr, $condition);
                $ratingUpdated++;
                $ratingBeforeSave = $this->ratingDataFactory->create()->load($ratingId);
                $ratingAfterSave = $this->ratingDataFactory->create()->load($ratingId)
                    ->setStatus(Rating::STATUS_NOT_APPROVED);
                if ($this->canDispatchEvaluationEvent($ratingBeforeSave, $ratingAfterSave)) {
                    $this->dispatchEvaluationEvent($ratingAfterSave);
                }
            }
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setRatingData($coditionData, ["status"=>3]);
        if ($ratingUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 rating(s) were disapproved.", $ratingUpdated));
        }
        return $resultRedirect->setPath("expressdelivery/rating/index");
    }

    /**
     * @param RatingInterface $beforeSave
     * @param RatingInterface $afterSave
     * @return bool
     */
    public function canDispatchEvaluationEvent(
        RatingInterface $beforeSave,
        RatingInterface $afterSave
    ): bool {
        return ($afterSave->getStatus() != Rating::STATUS_PENDING)
            && $beforeSave->getStatus() != $afterSave->getStatus();
    }

    /**
     * @param RatingInterface $review
     * @return bool
     */
    public function dispatchEvaluationEvent(RatingInterface $review): bool
    {
        try {
            $customerId = $review->getCustomerId();
            $deliveryboyId = $review->getDeliveryboyId();
            $customer = $this->customerF->create()->load($customerId);
            $deliveryboy = $this->deliveryboyResourceCollectionF->create()
                ->addFieldToFilter(
                    DeliveryboyInterface::ID,
                    $deliveryboyId
                )->getFirstItem();
            $storeId = $customer && $customer->getStoreId() ? $customer->getStoreId() : null;
            $customerName = $customer && $customer->getFirstname() ? $customer->getFirstname() : __("Guest");
            $ratingMaxLimit = ModuleGlobalConstants::RATING_MAX_VALUE;
            $ratingManagerName = ModuleGlobalConstants::DEFAULT_RATING_MANAGER_NAME;
            $ratingMaxLimit = ModuleGlobalConstants::RATING_MAX_VALUE;
            $ratingManagerName = ModuleGlobalConstants::DEFAULT_RATING_MANAGER_NAME;
            $senderInfo = [
                'name' => ModuleGlobalConstants::DEFAULT_ADMIN_NAME,
                'email' => $this->deliveryboyDataHelper->getConfigData(
                    ModuleGlobalConstants::DEFAULT_ADMIN_EMAIL_XML_PATH
                ),
            ];
            $receiversInfo = [
                'name' => $deliveryboy->getName(),
                'email' => $deliveryboy->getEmail(),
            ];
            $ratingStatusLabel = $this->deliveryboyDataHelper->getRatingStatuses()[$review->getStatus()];
            $review->setStatus($ratingStatusLabel);
            $this->_eventManager->dispatch(
                'inform_deliveryboy_review_evaluation_event',
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
        } catch (\Throwable $t) {
            $this->logger->debug($t->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_DeliveryBoy::rating");
    }
}
