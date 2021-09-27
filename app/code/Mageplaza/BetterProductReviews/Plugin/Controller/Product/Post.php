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

namespace Mageplaza\BetterProductReviews\Plugin\Controller\Product;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\Generic;
use Magento\Review\Controller\Product\Post as ReviewPost;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;

/**
 * Class Post
 * @package Mageplaza\BetterProductReviews\Plugin\Controller\Product
 */
class Post
{
    /**
     * @var Json
     */
    protected $_resultJson;

    /**
     * @var Generic
     */
    protected $_reviewSession;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Post constructor.
     *
     * @param Json $resultJson
     * @param Generic $reviewSession
     * @param ManagerInterface $messageManager
     * @param HelperData $helperData
     */
    public function __construct(
        Json $resultJson,
        Generic $reviewSession,
        ManagerInterface $messageManager,
        HelperData $helperData
    ) {
        $this->_resultJson = $resultJson;
        $this->_reviewSession = $reviewSession;
        $this->_messageManager = $messageManager;
        $this->_helperData = $helperData;
    }

    /**
     * @param ReviewPost $subject
     * @param $result
     *
     * @return $this|Redirect|mixed
     * @SuppressWarnings(Unused)
     */
    public function afterExecute(
        ReviewPost $subject,
        $result
    ) {
        if (!$this->_helperData->isEnabled()) {
            return $result;
        }
        if (!$this->_reviewSession->getFormData(true)) {
            $ajaxResult = [
                'success' => true,
                'responseMessage' => __('You submitted your review for moderation.')
            ];
        } else {
            $ajaxResult = [
                'success' => false,
                'responseMessage' => __('We can\'t post your review right now.')
            ];
        }
        if ($subject->getRequest()->isAjax()) {
            $this->_messageManager->getMessages()->clear();
        }

        return ($subject->getRequest()->isAjax())
            ? $this->_resultJson->setData($ajaxResult)
            : $result;
    }
}
