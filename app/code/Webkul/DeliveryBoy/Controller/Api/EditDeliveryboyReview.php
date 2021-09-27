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
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface as DeliveryboyInterface;

class EditDeliveryboyReview extends \Webkul\DeliveryBoy\Controller\Api\AbstractRating
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->validateRequest();
            $this->extractRequestData();
            $this->authorize();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $rating = $this->getRatingById($this->ratingId);
            
            if ($this->isTitleValid($this->title)) {
                $rating->setTitle($this->title);
            }
            if ($this->isCommentValid($this->comment)) {
                $rating->setComment($this->comment);
            }
            if ($this->isStatusValid($this->status)) {
                $rating->setStatus($this->status);
            }

            $rating->save();
            
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Review updated successfully.");
            $this->returnArray['rating'] = $rating->getData();
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }
        
        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function validateRequest()
    {
        if (!($this->getRequest()->getMethod() == "POST" && $this->wholeData)) {
            throw new LocalizedException(__("Invalid request."));
        }
    }

    /**
     * @return void
     */
    public function extractRequestData()
    {
        $this->storeId = trim($this->wholeData["storeId"] ??
                $this->storeManager->getDefaultStoreView()->getId());
        $this->pageNumber = trim($this->wholeData["pageNumber"] ?? 1);
        $this->ratingId = trim($this->wholeData["ratingId"] ?? 0);
        $this->title = trim($this->wholeData["title"] ?? "");
        $this->comment = trim($this->wholeData["comment"] ?? "");
        $this->status = trim($this->wholeData["status"] ?? -1);
        $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    protected function authorize(): bool
    {
        if (!$this->isAdmin()) {
            throw new LocalizedException(__('Unauthorized access.'));
        }

        return true;
    }

    /**
     * @param int $deliveryboyId
     * @return bool
     */
    protected function isDeliveryboyExists(int $deliveryboyId): bool
    {
        return $this->deliveryboyResourceCollection->create()
            ->addFieldToFilter(DeliveryboyInterface::ID, $deliveryboyId)
            ->getFirstItem()->getId() == $deliveryboyId;
    }

    /**
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }

    /**
     * @param int $statusId
     * @return bool
     */
    protected function isStatusValid(int $statusId): bool
    {
        return (in_array($statusId, $this->getRatingStatusValueArray()));
    }
    
    /**
     * @param string $title
     * @return bool
     */
    protected function isTitleValid(string $title): bool
    {
        return (!empty($title) && (strlen($title) >= 3));
    }
    
    /**
     * @param string $comment
     * @return bool
     */
    protected function isCommentValid(string $comment): bool
    {
        return !empty($comment);
    }

    /**
     * @param int $ratingId
     * @return \Webkul\Deliveryboy\Api\Data\RatingInterface
     * @throws NoSuchEntityException
     */
    protected function getRatingById(
        int $ratingId
    ): \Webkul\Deliveryboy\Api\Data\RatingInterface {
        $rating = $this->ratingFactory->create()->load($ratingId);
        if (!$ratingId || $rating->getId() != $ratingId) {
            throw new NoSuchEntityException(__("Invalid rating."));
        }

        return $rating;
    }
}
