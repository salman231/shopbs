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
 * @category    Mageplaza
 * @package     Mageplaza_BetterProductReviews
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Api;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class  ProductReviewsRepositoryInterface
 * @package Mageplaza\BetterProductReviews\Api
 */
interface ProductReviewsRepositoryInterface
{
    /**
     * @return \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface[]
     * @throws Exception
     */
    public function getAllReviews();

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface[]
     * @throws NoSuchEntityException
     */
    public function getListReviews(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $reviewId
     *
     * @return \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface[]
     * @throws Exception
     */
    public function getReviewById($reviewId);

    /**
     * @param int $productId
     *
     * @return \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface[]
     * @throws Exception
     */
    public function getReviewByProductId($productId);

    /**
     * @param string $productSku
     *
     * @return \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface[]
     * @throws Exception
     */
    public function getReviewByProductSku($productSku);

    /**
     * @param int $customerId
     *
     * @return \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface[]
     * @throws Exception
     */
    public function getReviewByCustomerId($customerId);

    /**
     * @param int $reviewId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProductByReviewId($reviewId);

    /**
     * @param int $productId
     * @param \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface $review
     *
     * @return \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface
     * @throws LocalizedException
     * @throws Exception
     */
    public function addReview($productId, $review);

    /**
     * @param int $reviewId
     * @param \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface $review
     *
     * @return \Mageplaza\BetterProductReviews\Api\Data\ReviewInterface
     * @throws LocalizedException
     * @throws Exception
     */
    public function updateReview($reviewId, $review);

    /**
     * @param int $statusId
     * @param string $reviewIds
     *
     * @return string
     */
    public function updateMultiReview($statusId, $reviewIds);

    /**
     * @param int $reviewId
     *
     * @return string
     * @throws Exception
     */
    public function deleteReview($reviewId);
}
