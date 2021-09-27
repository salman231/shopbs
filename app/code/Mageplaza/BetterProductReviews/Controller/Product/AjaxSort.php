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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Layout;

/**
 * Class AjaxSort
 *
 * @package Mageplaza\BetterProductReviews\Controller\Product
 */
class AjaxSort extends Action
{
    /**
     * @var ForwardFactory
     */
    protected $_resultFwFactory;

    /**
     * @var Json
     */
    protected $_resultJson;

    /**
     * @var Layout
     */
    protected $_layout;

    /**
     * AjaxSort constructor.
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param Json $resultJson
     * @param Layout $layout
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        Json $resultJson,
        Layout $layout
    ) {
        $this->_resultFwFactory = $resultForwardFactory;
        $this->_resultJson = $resultJson;
        $this->_layout = $layout;

        parent::__construct($context);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $params = $this->getRequest()->getParams();
            $reviewOffset = 0;

            if (isset($params['review_offset'])) {
                $reviewOffset = (int)$params['review_offset'] + (int)$params['review_per_page'];
            }
            $sortType = (isset($params['sort_type'])) ? $params['sort_type'] : null;

            $result = [
                'review_list' => $this->_layout->createBlock('Mageplaza\BetterProductReviews\Block\Review\ListView')
                    ->setTemplate('Mageplaza_BetterProductReviews::review/list/details.phtml')
                    ->setSortType($sortType)
                    ->setReviewLoaded()
                    ->setReviewOffset($reviewOffset)
                    ->setAjaxProductId($params['product_id'])
                    ->toHtml(),
                'success' => true
            ];

            return $this->_resultJson->setData($result);
        }

        return $this->_resultFwFactory->create()->forward('noroute');
    }
}
