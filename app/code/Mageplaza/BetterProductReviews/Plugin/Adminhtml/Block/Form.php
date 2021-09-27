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

namespace Mageplaza\BetterProductReviews\Plugin\Adminhtml\Block;

use Closure;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Model\Auth;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Review\Model\Review;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use Mageplaza\BetterProductReviews\Model\Config\Source\BuyerType;
use Mageplaza\BetterProductReviews\Model\Reply;
use Mageplaza\BetterProductReviews\Model\ReplyFactory;

/**
 * Class Form
 *
 * @package Mageplaza\BetterProductReviews\Plugin\Adminhtml\Block
 */
class Form
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var UserFactory
     */
    protected $_userFactory;

    /**
     * @var Auth
     */
    protected $_auth;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ReplyFactory
     */
    protected $_replyFactory;

    /**
     * @var BuyerType
     */
    protected $_buyerType;

    /**
     * Form constructor.
     *
     * @param Registry $coreRegistry
     * @param Yesno $yesno
     * @param UserFactory $userFactory
     * @param Auth $auth
     * @param HelperData $helperData
     * @param ReplyFactory $replyFactory
     * @param BuyerType $buyerType
     */
    public function __construct(
        Registry $coreRegistry,
        Yesno $yesno,
        UserFactory $userFactory,
        Auth $auth,
        HelperData $helperData,
        ReplyFactory $replyFactory,
        BuyerType $buyerType
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_yesNo = $yesno;
        $this->_userFactory = $userFactory;
        $this->_auth = $auth;
        $this->_helperData = $helperData;
        $this->_replyFactory = $replyFactory;
        $this->_buyerType = $buyerType;
    }

    /**
     * @param Generic $subject
     * @param Closure $proceed
     * @param string $detailsFieldSetId
     *
     * @return Closure
     * @throws LocalizedException
     */
    public function addReviewExtraFields($subject, $proceed, $detailsFieldSetId)
    {
        $form = $subject->getForm();

        if (is_object($form) && $this->_helperData->isEnabled()) {
            /**
             * @var Review $review
             */
            $review = $this->_coreRegistry->registry('review_data');

            $detailsFieldset = $form->getElement($detailsFieldSetId);

            $detailsFieldset->addField(
                'mp_bpr_images',
                '\Mageplaza\BetterProductReviews\Block\Adminhtml\Review\Edit\Form\Renderer\Images',
                [
                    'name' => 'mp_bpr_images',
                    'label' => 'Review Image(s)'
                ]
            );

            $detailsFieldset->addField(
                'mp_bpr_recommended_product',
                'select',
                [
                    'name' => 'mp_bpr_recommended_product',
                    'label' => __('Recommendation Display'),
                    'title' => __('Recommendation Display'),
                    'values' => $this->_yesNo->toOptionArray(),
                    'value' => ($review) ? $review->getMpBprRecommendedProduct() : '1'
                ]
            );
            if ($review) {
                $verifiedText = ($review->getMpBprVerifiedBuyer())
                    ? '<span class="mp-success">' . __('Verified') . '</span>'
                    : '<span class="mp-danger">' . __('Not Verified') . '</span>';

                $detailsFieldset->addField(
                    'mp_bpr_verified_buyer',
                    'note',
                    [
                        'label' => __('Verified Buyer'),
                        'text' => $verifiedText
                    ]
                );
            } else {
                $detailsFieldset->addField(
                    'mp_bpr_verified_buyer',
                    'select',
                    [
                        'name' => 'mp_bpr_verified_buyer',
                        'label' => __('Verified Buyer'),
                        'title' => __('Verified Buyer'),
                        'values' => $this->_buyerType->toOptionArray(),
                    ]
                );
            }

            if ($review) {
                /**
                 * @var Reply $reply
                 */
                $reply = $this->_replyFactory->create();
                $replyData = $reply->getResource()->getReplyByReviewId($review->getId());
            } else {
                $replyData = null;
            }

            if ($replyData) {
                /**
                 * @var User $user
                 */
                $user = $this->_userFactory->create()->load($replyData['user_id']);
                $userName = $user->getUserName();
                $userId = $user->getId();
                $nickName = $replyData['reply_nickname'];
                $replyContent = $replyData['reply_content'];
                $isReply = $replyData['reply_enabled'];
            } else {
                /**
                 * @var User $user
                 */
                $user = $this->_auth->getUser();
                $userName = $user->getUserName();
                $userId = $user->getId();
                $replyContent = '';
                $nickName = '';
                $isReply = 0;
            }

            $mpTfaFieldset = $form->addFieldset('mp_bpr_reply', ['legend' => __('Admin Reply')]);

            $mpTfaFieldset->addField(
                'user_id',
                'hidden',
                [
                    'name' => 'user_id',
                    'value' => $userId
                ]
            );

            $mpTfaFieldset->addField(
                'user_name',
                'note',
                [
                    'label' => __('Admin User'),
                    'text' => $userName,
                    'note' => 'Visible to admin only'
                ]
            );

            $mpTfaFieldset->addField(
                'reply_enabled',
                'select',
                [
                    'name' => 'reply_enabled',
                    'label' => __('Write a Reply'),
                    'title' => __('Write a Reply'),
                    'values' => $this->_yesNo->toOptionArray(),
                    'value' => $isReply
                ]
            );

            $mpTfaFieldset->addField(
                'reply_nickname',
                'text',
                [
                    'name' => 'reply_nickname',
                    'label' => __('Nickname'),
                    'title' => __('Nickname'),
                    'required' => true,
                    'value' => $nickName
                ]
            );

            $mpTfaFieldset->addField(
                'reply_content',
                'textarea',
                [
                    'name' => 'reply_content',
                    'label' => __('Comment'),
                    'title' => __('Comment'),
                    'required' => true,
                    'value' => $replyContent
                ]
            );

            $subject->setChild(
                'form_after',
                $subject->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                    ->addFieldMap('reply_enabled', 'reply_enabled')
                    ->addFieldMap('reply_nickname', 'reply_nickname')
                    ->addFieldMap('reply_content', 'reply_content')
                    ->addFieldDependence('reply_nickname', 'reply_enabled', '1')
                    ->addFieldDependence('reply_content', 'reply_enabled', '1')
            );

            $subject->setForm($form);
        }

        return $proceed();
    }
}
