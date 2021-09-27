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
use Webkul\DeliveryBoy\Controller\RegistryConstants;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Webkul\DeliveryBoy\Controller\Adminhtml\Rating
{
    /**
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $ratingId = $this->initRating();
            $ratingData = [];
            $rating = $this->ratingRepository->getById($ratingId);
            $result = $rating->getData();
            if (!empty($result)) {
                $ratingData = $result;
                $ratingData[RatingInterface::ID] = $ratingId;
                $this->_getSession()->setRatingFormData($ratingData);
                $this->coreRegistry->register("review_data", $rating);
            } else {
                $this->messageManager->addError(__("Requested rating doesn't exist"));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("expressdelivery/rating/index");
                return $resultRedirect;
            }
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu("Webkul_DeliveryBoy::rating");
            $resultPage->getConfig()->getTitle()->prepend(__("Approve Rating \"%1\"", $ratingData["title"]));
            $resultPage->addContent(
                $resultPage->getLayout()->createBlock(
                    \Webkul\DeliveryBoy\Block\Adminhtml\Rating\Edit::class
                )
            );
            return $resultPage;
        } catch (\Throwable $e) {
            $this->messageManager->addException($e, __("Something went wrong while approving rating."));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath("expressdelivery/rating/index");
            return $resultRedirect;
        }
    }

    /**
     * @return int|null
     */
    protected function initRating()
    {
        $ratingId = (int)$this->getRequest()->getParam("id");
        if ($ratingId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_RATING_ID, $ratingId);
        }
        return $ratingId;
    }
}
