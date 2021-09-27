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

namespace Mageplaza\BetterProductReviews\Plugin\Adminhtml\Block\Form;

use Closure;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\BetterProductReviews\Plugin\Adminhtml\Block\Form;

/**
 * Class Form
 *
 * @package Mageplaza\BetterProductReviews\Plugin\Adminhtml\Block\Edit
 */
class Add extends Form
{
    const REVIEW_DETAILS_FIELD_SET_ID = 'add_review_form';

    /**
     * @param \Magento\Review\Block\Adminhtml\Add\Form $subject
     * @param Closure $proceed
     *
     * @return Closure
     * @throws LocalizedException
     */
    public function aroundGetFormHtml(
        \Magento\Review\Block\Adminhtml\Add\Form $subject,
        Closure $proceed
    ) {
        return $this->addReviewExtraFields($subject, $proceed, self::REVIEW_DETAILS_FIELD_SET_ID);
    }
}
