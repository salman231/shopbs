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

namespace Mageplaza\BetterProductReviews\Model\ResourceModel\Review\Product;

use Magento\Framework\App\ObjectManager;
use Magento\Reports\Model\ResourceModel\Review\Product\Collection as ProductCollection;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use Mageplaza\BetterProductReviews\Model\Config\Source\System\ReviewStatus;
use Zend_Db_Expr;

/**
 * Class Collection
 *
 * @package Mageplaza\BetterProductReviews\Model\ResourceModel\Review\Product
 */
class Collection extends ProductCollection
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Join review table to result
     *
     * @return $this
     */
    protected function _joinReview()
    {
        $objectManager = ObjectManager::getInstance();
        $this->_helperData = $objectManager->create('Mageplaza\BetterProductReviews\Helper\Data');
        $configReviewStatus = (int)$this->_helperData->getConfigGeneral('review_status');

        $subSelect = clone $this->getSelect();
        $subSelect->reset()->from(
            ['rev' => $this->getTable('review')],
            'COUNT(DISTINCT rev.review_id)'
        )->where(
            'e.entity_id = rev.entity_pk_value'
        );

        if ($configReviewStatus !== ReviewStatus::BOTH && $this->_helperData->isEnabled()) {
            $subSelect->where("rev.status_id = '" . $configReviewStatus . "'");
        }
        $this->addAttributeToSelect('name');

        $this->getSelect()->join(
            ['r' => $this->getTable('review')],
            'e.entity_id = r.entity_pk_value',
            [
                'review_cnt' => new Zend_Db_Expr(sprintf('(%s)', $subSelect)),
                'created_at' => 'MAX(r.created_at)'
            ]
        )->group(
            'e.entity_id'
        );

        $joinCondition = [
            'e.entity_id = table_rating.entity_pk_value',
            $this->getConnection()->quoteInto('table_rating.store_id > ?', 0),
        ];

        $sumPercentField = new Zend_Db_Expr('SUM(table_rating.percent)');
        $sumPercentApproved = new Zend_Db_Expr('SUM(table_rating.percent_approved)');
        $countRatingId = new Zend_Db_Expr('COUNT(table_rating.rating_id)');

        $this->getSelect()->joinLeft(
            ['table_rating' => $this->getTable('rating_option_vote_aggregated')],
            implode(' AND ', $joinCondition),
            [
                'avg_rating' => new Zend_Db_Expr(sprintf('%s/%s', $sumPercentField, $countRatingId)),
                'avg_rating_approved' => new Zend_Db_Expr(sprintf('%s/%s', $sumPercentApproved, $countRatingId))
            ]
        );

        return $this;
    }
}
