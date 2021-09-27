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

namespace Mageplaza\BetterProductReviews\Observer\Model\Review;

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use Mageplaza\BetterProductReviews\Helper\Image as HelperImage;
use Mageplaza\BetterProductReviews\Model\Reply;
use Mageplaza\BetterProductReviews\Model\ReplyFactory;

/**
 * Class Save
 * @package Mageplaza\BetterProductReviews\Model\Review\Save
 */
class Save implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var HelperImage
     */
    protected $_helperImage;

    /**
     * @var ReplyFactory
     */
    protected $_replyFactory;

    /**
     * Save constructor.
     *
     * @param HelperData $helperData
     * @param HelperImage $helperImage
     * @param ReplyFactory $replyFactory
     */
    public function __construct(
        HelperData $helperData,
        HelperImage $helperImage,
        ReplyFactory $replyFactory
    ) {
        $this->_helperData = $helperData;
        $this->_helperImage = $helperImage;
        $this->_replyFactory = $replyFactory;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->_helperData->isEnabled()) {
            $objectData = $observer->getObject()->getData();
            /**
             * Save review admin reply
             * @var Reply $reply
             */
            $reply = $this->_replyFactory->create();
            $this->_helperImage->uploadImages($objectData);

            if ($this->checkReplyReview($objectData['review_id'])) {
                $reply = $this->getReplyByReviewId($objectData['review_id']);
                $data = $objectData;
                unset($data['review_id']);
                $reply->addData($data)->save();
            } elseif (isset($objectData['reply_enabled']) && $objectData['reply_enabled']) {
                $reply->setReviewId($objectData['review_id'])
                    ->addData($objectData)
                    ->save();
            }

            /**
             * Save review details extra fields
             */
            $reply->getResource()->saveReviewExtraFields($objectData['review_id'], $objectData);
        }
    }

    /**
     * @param int $reviewId
     *
     * @return bool
     */
    public function checkReplyReview($reviewId)
    {
        $replyCollection = $this->_replyFactory->create()->getCollection();

        return $replyCollection->addFieldToFilter('review_id', $reviewId)->count() > 0;
    }

    /**
     * @param $reviewId
     *
     * @return DataObject
     */
    public function getReplyByReviewId($reviewId)
    {
        $replyCollection = $this->_replyFactory->create()->getCollection();

        return $replyCollection->addFieldToFilter('review_id', $reviewId)->getFirstItem();
    }
}
