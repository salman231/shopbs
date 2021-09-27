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

use Webkul\DeliveryBoy\Controller\RegistryConstants;
use Webkul\DeliveryBoy\Api\Data\RatingInterface;
use Webkul\DeliveryBoy\Helper\ModuleGlobalConstants;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;
use Webkul\DeliveryBoy\Model\Rating;

class Save extends \Webkul\DeliveryBoy\Controller\Adminhtml\Rating
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $requestData = $this->getRequest()->getPostValue();
        $ratingId = $this->getRequest()->getParam("id") ?? null;
        if ($requestData) {
            try {
                $ratingData = $requestData;
                $isExistingRating = (bool) $ratingId;
                $rating = $this->ratingDataFactory->create();
                if ($isExistingRating) {
                    $ratingData["id"] = $ratingId;
                    $ratingBeforeSave = $this->ratingDataFactory->create()->load($ratingId);
                }
                $rating->setData($ratingData);
                // Save Rating ////////////////////////////////////////////////////////////////////
                if ($isExistingRating) {
                    $this->ratingRepository->save($rating);
                    if ($this->canDispatchEvaluationEvent($ratingBeforeSave, $rating)) {
                        $rating = $this->collectionFactory->create()->addFieldToFilter(
                            RatingInterface::ID,
                            $rating->getId()
                        )->getFirstItem();
                        $this->dispatchEvaluationEvent($rating);
                    }
                }
                $this->coreRegistry->register(RegistryConstants::CURRENT_RATING_ID, $ratingId);
                $this->messageManager->addSuccess(__("Rating is updated."));
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setRatingFormData($requestData);
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __("Something went wrong while saving the rating. %1", $exception->getMessage())
                );
                $this->_getSession()->setRatingFormData($requestData);
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath("expressdelivery/rating/index");
        
        return $resultRedirect;
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
}
