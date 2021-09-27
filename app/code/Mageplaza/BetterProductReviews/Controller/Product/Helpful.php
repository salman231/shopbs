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

namespace Mageplaza\BetterProductReviews\Controller\Product;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Mageplaza\BetterProductReviews\Model\Reply;
use Mageplaza\BetterProductReviews\Model\ReplyFactory;

/**
 * Class Helpful
 *
 * @package Mageplaza\BetterProductReviews\Controller\Product
 */
class Helpful extends Action
{
    /**
     * @var ForwardFactory
     */
    protected $_resultFwFactory;

    /**
     * @var ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var Json
     */
    protected $_resultJson;

    /**
     * @var ReplyFactory
     */
    protected $_replyFactory;

    /**
     * Helpful constructor.
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param ReviewFactory $reviewFactory
     * @param Json $resultJson
     * @param ReplyFactory $replyFactory
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        ReviewFactory $reviewFactory,
        Json $resultJson,
        ReplyFactory $replyFactory
    ) {
        $this->_resultFwFactory = $resultForwardFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_resultJson = $resultJson;
        $this->_replyFactory = $replyFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Forward|ResultInterface|Page
     * @throws Exception
     */
    public function execute()
    {
        $reviewId = $this->getRequest()->getParam('review_id');
        /**
         * @var Review $review
         */
        $review = $this->_reviewFactory->create()->load($reviewId);
        /** @var Reply $reply */
        $reply = $this->_replyFactory->create();

        if ($this->getRequest()->isAjax()) {
            try {
                $currentHelpful = (int)$review->getMpBprHelpful();
                $data['mp_bpr_helpful'] = $currentHelpful + 1;
                $reply->getResource()->saveReviewExtraFields($review->getId(), $data);

                $result = [
                    'success' => true,
                    'helpful_count' => $data['mp_bpr_helpful']
                ];
            } catch (Exception $e) {
                $result = ['success' => false, 'error' => $e->getMessage()];
            }

            return $this->_resultJson->setData($result);
        }

        return $this->_resultFwFactory->create()->forward('noroute');
    }
}
