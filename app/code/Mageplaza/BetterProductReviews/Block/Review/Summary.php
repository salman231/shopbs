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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\Review as ReviewModel;
use Mageplaza\BetterProductReviews\Block\Review;

/**
 * Class Summary
 *
 * @package Mageplaza\BetterProductReviews\Block\Review
 */
class Summary extends Review
{
    /**
     * Get review summary is enabled config
     *
     * @return string
     */
    public function isReviewSummaryEnabled()
    {
        return $this->_helperData->getConfigGeneral('review_summary');
    }

    /**
     * @param $product
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getRatingSummary($product)
    {
        return $this->_helperData->getRatingSummary($product);
    }

    /**
     * @param $product
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getReviewCount($product)
    {
        return $this->_helperData->getReviewCount($product);
    }

    /**
     * @return \Mageplaza\BetterProductReviews\Model\ResourceModel\Review\Collection
     * @throws NoSuchEntityException
     */
    public function getReviewsCollection()
    {
        $reviewsCollection = $this->_reviewsColFactory->create()->addReviewDetailTable()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addStatusFilter(ReviewModel::STATUS_APPROVED)
            ->addEntityFilter('product', $this->getProduct()->getId())
            ->addRateVotes()->setDateOrder();

        return $reviewsCollection;
    }

    /**
     * Get Totals rating vote count
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getReviewsVotingCount()
    {
        return $this->getReviewsCollection()->addVotingTable($this->_storeManager->getStore()->getId())->getSize();
    }

    /**
     * Get rating vote count depends on specific voting value
     *
     * @param $votingValue
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getReviewsVotingCountByValue($votingValue)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        /**
         * @var Collection $votingCollection
         */
        $votingCollection = $this->getReviewsCollection()->addVotingFilter($votingValue, $storeId);

        return $votingCollection->getSize();
    }

    /**
     * Get rating vote percent depends on specific voting value
     *
     * @param $votingValue
     *
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getReviewsVotingPercentByValue($votingValue)
    {
        $votingPercent = 0;
        $currentVotes = $this->getReviewsVotingCountByValue($votingValue);
        $totalVotes = $this->getReviewsVotingCount();
        if ($totalVotes) {
            $votingPercent = round($currentVotes / $totalVotes * 100, 2);
        }

        return $votingPercent;
    }

    /**
     * Get totals recommend product
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getReviewsRecommendCount()
    {
        return $this->getReviewsCollection()
            ->addFieldToFilter('mp_bpr_recommended_product', ['notnull' => true])
            ->getSize();
    }

    /**
     * Get totals recommended product
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getReviewsRecommendedCount()
    {
        return $this->getReviewsCollection()
            ->addFieldToFilter('mp_bpr_recommended_product', ['eq' => 1])
            ->getSize();
    }

    /**
     * Get review customer recommend percent
     *
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getReviewsRecommendPercent()
    {
        $recommendPercent = 0;
        if ($this->getReviewsRecommendCount() > 0) {
            $recommendPercent = round($this->getReviewsRecommendedCount() / $this->getReviewsRecommendCount() * 100);
        }

        return $recommendPercent;
    }
}
