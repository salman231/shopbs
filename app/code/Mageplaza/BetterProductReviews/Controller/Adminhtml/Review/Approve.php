<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Controller\Adminhtml\Review;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Review\Model\Review;

/**
 * Class Approve
 *
 * @package Mageplaza\BetterProductReviews\Controller\Adminhtml\Review
 */
class Approve extends ProductController
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        /**
         * @var Redirect $resultRedirect
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $reviewId = $this->getRequest()->getParam('id', false);
        $nextId = $this->getRequest()->getParam('next_item', false);
        try {
            $review = $this->reviewFactory->create()->load($reviewId);
            $review->setStatusId(Review::STATUS_APPROVED)->save();
            $review->aggregate();
            $this->messageManager->addSuccessMessage(__('The review has been approved.'));

            $resultRedirect->setPath('review/product/edit', ['id' => $nextId]);

            return $resultRedirect;
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong approving this review.'));
        }

        return $resultRedirect->setPath('review/product/edit/', ['id' => $reviewId]);
    }
}
