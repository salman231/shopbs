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

namespace Mageplaza\BetterProductReviews\Block\Review;

use Magento\Framework\Phrase;
use Mageplaza\BetterProductReviews\Block\Review;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;

/**
 * Class Summary
 *
 * @package Mageplaza\BetterProductReviews\Block\Review
 */
class Form extends Review
{
    /**
     * Get upload images enabled config
     *
     * @return string
     */
    public function isUploadImageEnabled()
    {
        return $this->_helperData->getWriteReviewConfig('upload_images');
    }

    /**
     * @return string
     */
    public function getJsonLimitUploadImage()
    {
        $limit = (int)$this->_helperData->getWriteReviewConfig('upload_limit');
        $result = ($limit) ?: false;

        return HelperData::jsonEncode($result);
    }

    /**
     * @return string
     */
    public function getLimitUploadImage()
    {
        return $this->_helperData->getWriteReviewConfig('upload_limit');
    }

    /**
     * Get term & conditions enabled config
     *
     * @return string
     */
    public function isTermConditionsEnabled()
    {
        return $this->_helperData->getWriteReviewConfig('term_conditions/enabled');
    }

    /**
     * Get term title
     *
     * @return string
     */
    public function getTermTitle()
    {
        $anchor = '<a href="' . $this->getTermAnchorURL() . '">' . $this->getTermAnchorText() . '</a>';
        $defaultTitle = __('I agree with the %1.', $anchor);
        $title = str_replace('{{anchor}}', $anchor, $this->_helperData
            ->getWriteReviewConfig('term_conditions/title'));

        return ($this->_helperData->getWriteReviewConfig('term_conditions/title')) ? $title : $defaultTitle;
    }

    /**
     * Get term anchor text
     *
     * @return Phrase|string
     */
    public function getTermAnchorText()
    {
        return ($this->_helperData->getWriteReviewConfig('term_conditions/anchor_text')) ?: __('Terms and Conditions');
    }

    /**
     * Get term anchor URL
     *
     * @return string
     */
    public function getTermAnchorURL()
    {
        return ($this->_helperData->getWriteReviewConfig('term_conditions/anchor_url')) ?: '#';
    }

    /**
     * Get term checked by default
     *
     * @return string
     */
    public function getTermIsChecked()
    {
        return $this->_helperData->getWriteReviewConfig('term_conditions/is_checked');
    }

    /**
     * @return string
     */
    public function getAjaxUploadImageUrl()
    {
        return $this->getUrl('mpbetterproductreviews/product/upload');
    }
}
