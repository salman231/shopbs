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

namespace Mageplaza\BetterProductReviews\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Reply
 *
 * @method  Reply setReviewId($reviewId)
 * @package Mageplaza\BetterProductReviews\Model
 */
class Reply extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_betterproductreviews_review_reply';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_betterproductreviews_review_reply';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_betterproductreviews_review_reply';

    /**
     * @var string
     */
    protected $_idFieldName = 'reply_id';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\BetterProductReviews\Model\ResourceModel\Reply');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
