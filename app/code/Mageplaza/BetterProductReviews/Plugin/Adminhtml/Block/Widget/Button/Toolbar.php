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

namespace Mageplaza\BetterProductReviews\Plugin\Adminhtml\Block\Widget\Button;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar as ButtonToolbar;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Review\Helper\Action\Pager;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;

/**
 * Class PdfInvoiceButtonToolbar
 * @package Mageplaza\PdfInvoice\Plugin
 */
class Toolbar
{
    /**
     * Review action pager
     *
     * @var Pager
     */
    protected $_reviewActionPager = null;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Toolbar constructor.
     *
     * @param Pager $reviewActionPager
     * @param HelperData $helperData
     */
    public function __construct(
        Pager $reviewActionPager,
        HelperData $helperData
    ) {
        $this->_reviewActionPager = $reviewActionPager;
        $this->_helperData = $helperData;
    }

    /**
     * @param ButtonToolbar $subject
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @SuppressWarnings(Unused)
     */
    public function beforePushButtons(
        ButtonToolbar $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ) {
        $request = $context->getRequest();
        /**
         * @var $actionPager Pager
         */
        $actionPager = $this->_reviewActionPager;
        $actionPager->setStorageId('reviews');
        $reviewId = $request->getParam('id');
        $nextId = $actionPager->getNextItemId($reviewId);

        if ($nextId !== false && $request->getFullActionName() === 'review_product_edit') {
            $buttonList->add(
                'approve_and_next',
                [
                    'label' => __('Approve and Next'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'target' => '#edit_form',
                            ],
                        ],
                    ],
                    'onclick' => "setLocation('" . $context
                            ->getUrl('mpbetterproductreviews/review/approve', [
                                'id' => $reviewId,
                                'next_item' => $nextId
                            ]) . "')"
                ],
                3,
                50
            );
        }
    }
}
