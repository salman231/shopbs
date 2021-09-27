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

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewsColFactory;
use Magento\Review\Model\Review as ReviewModel;
use Mageplaza\BetterProductReviews\Block\Review;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use Mageplaza\BetterProductReviews\Helper\Image as HelperImage;
use Mageplaza\BetterProductReviews\Model\Config\Source\System\SortType;
use Mageplaza\BetterProductReviews\Model\ResourceModel\Review\Collection as ReviewCollection;

/**
 * Class ListView
 *
 * @method string getSortType()
 * @method int getReviewOffset()
 * @method int getTotalReviews()
 * @method bool getIsLoadMore()
 * @method ListView setIsLoadMore(bool $flag)
 * @method ListView setReviewOffset(int $reviewOffset)
 * @method ListView setTotalReviews(int $totalReview)
 * @package Mageplaza\BetterProductReviews\Block\Review
 */
class ListView extends Review
{
    /**
     * Review collection
     *
     * @var ReviewCollection
     */
    protected $_reviewsCollection;

    /**
     * @var SortType
     */
    protected $_sortingType;

    /**
     * ListView constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ReviewsColFactory $reviewsColFactory
     * @param ProductRepositoryInterface $productRepository
     * @param HelperData $helperData
     * @param HelperImage $helperImage
     * @param SortType $sortType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ReviewsColFactory $reviewsColFactory,
        ProductRepositoryInterface $productRepository,
        HelperData $helperData,
        HelperImage $helperImage,
        SortType $sortType,
        array $data = []
    ) {
        $this->_sortingType = $sortType;

        parent::__construct(
            $context,
            $coreRegistry,
            $reviewsColFactory,
            $productRepository,
            $helperData,
            $helperImage,
            $data
        );
    }

    /**
     * @param ReviewModel $review
     *
     * @return array|null
     */
    public function getReviewImages($review)
    {
        $images = $review->getMpBprImages();
        if ($images) {
            try {
                $images = HelperData::jsonDecode($images);
            } catch (Exception $e) {
                $images = [];
            }
        }

        return $images;
    }

    /**
     * @param string $image
     *
     * @return string
     */
    public function getImageUrl($image)
    {
        $imageFile = $this->_helperImage->getMediaPath($image);

        return $this->_helperImage->getMediaUrl($imageFile);
    }

    /**
     * Get helpful is enabled config
     *
     * @return string
     */
    public function isHelpfulEnabled()
    {
        return $this->_helperData->getReviewListingConfig('enabled_helpful');
    }

    /**
     * Get store owner reply is enabled config
     *
     * @return string
     */
    public function isStoreOwnerReplyEnabled()
    {
        return $this->_helperData->getReviewListingConfig('store_owner_answer');
    }

    /**
     * Get sorting is enabled config
     *
     * @return string
     */
    public function isSortingEnabled()
    {
        return $this->_helperData->getReviewListingConfig('sorting/enabled');
    }

    /**
     * Get social share is enabled config
     *
     * @return string
     */
    public function isSocialShareEnabled()
    {
        return $this->_helperData->getReviewListingConfig('social_share');
    }

    /**
     * Get verified buyer mark show config
     *
     * @return string
     */
    public function isVerifiedBuyerMarkShow()
    {
        return $this->_helperData->getReviewListingConfig('verified_buyer');
    }

    /**
     * Get review nickname show config
     *
     * @return string
     */
    public function isReviewNicknameShow()
    {
        return $this->_helperData->getReviewListingConfig('show_nickname');
    }

    /**
     * Get review date show config
     *
     * @return string
     */
    public function isReviewDateShow()
    {
        return $this->_helperData->getReviewListingConfig('review_date');
    }

    /**
     * Get sorting type config
     *
     * @return array
     */
    public function getSortingTypeConfig()
    {
        $sortingTypes = [];
        $sortingTypeConfig = $this->_helperData->getReviewListingConfig('sorting/type');
        $sortingTypesConfig = explode(',', $sortingTypeConfig);
        $allTypes = $this->_sortingType->toOptionArray();
        foreach ($allTypes as $type) {
            if (in_array((string)$type['value'], $sortingTypesConfig, true)) {
                $sortingTypes[] = $type;
            }
        }

        return $sortingTypes;
    }

    /**
     * Get default sorting type config
     *
     * @return string
     */
    public function getDefaultSortingType()
    {
        return $this->_helperData->getReviewListingConfig('sorting/default_sorting');
    }

    /**
     * Get default sorting increase/decrease config
     *
     * @return string
     */
    public function getDefaultSortingDirection()
    {
        return $this->_helperData->getReviewListingConfig('sorting/default_sort_direction');
    }

    /**
     * Get review number per page
     *
     * @return int
     */
    public function getReviewsPerPage()
    {
        return ((int)$this->_helperData->getReviewListingConfig('items_per_page')) ?: 10;
    }

    /**
     * @return ReviewCollection
     * @throws NoSuchEntityException
     */
    public function getReviewsCollection()
    {
        if (null === $this->_reviewsCollection) {
            $productId = ($this->getProductId()) ?: $this->getAjaxProductId();
            $this->_reviewsCollection = $this->_reviewsColFactory->create()->addReviewDetailTable()
                ->addReviewReplyTable()
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addStatusFilter(ReviewModel::STATUS_APPROVED)
                ->addEntityFilter('product', $productId)
                ->addAverageVotingTable();

            $direction = $this->getDefaultSortingDirection();
            $defaultSortingType = ($this->getSortType()) ?: (int)$this->getDefaultSortingType();
            switch ($defaultSortingType) {
                case SortType::NEWEST:
                    $this->_reviewsCollection->setDateOrder($direction);
                    break;
                case SortType::HIGH_RATING:
                    $this->_reviewsCollection->setVotingOrder($direction);
                    break;

                case SortType::HELPFULNESS:
                    $this->_reviewsCollection->setHelpfulnessOrder($direction);
                    break;
            }
        }

        $this->setTotalReviews($this->_reviewsCollection->getSize());
        if ($this->getReviewOffset() < ($this->getTotalReviews() - $this->getReviewsPerPage())) {
            $this->setIsLoadMore(true);
        } else {
            $this->setIsLoadMore(false);
        }
        $this->_reviewsCollection->setLimitCollection($this->getReviewsPerPage(), $this->getReviewOffset());

        return $this->_reviewsCollection;
    }

    /**
     * Add rate votes
     *
     * @return Review
     * @throws NoSuchEntityException
     */
    protected function _beforeToHtml()
    {
        $this->getReviewsCollection()->load()->addRateVotes();

        return parent::_beforeToHtml();
    }

    /**
     * @return string
     */
    public function getAjaxHelpfulUrl()
    {
        return $this->getUrl('mpbetterproductreviews/product/helpful');
    }

    /**
     * @return string
     */
    public function getAjaxSortUrl()
    {
        return $this->getUrl('mpbetterproductreviews/product/ajaxsort');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getDataConfigHelpful()
    {
        $data = [
            'storeId' => $this->getCurrentStoreId(),
            'ajaxHelpfulUrl' => $this->getAjaxHelpfulUrl(),
        ];

        return HelperData::jsonEncode($data);
    }

    /**
     * @param string $reviewId
     *
     * @return mixed
     */
    public function getReviewRatingSummary($reviewId)
    {
        return $this->_helperData->getReviewRatingSummary($reviewId);
    }

    /**
     * Get verified buyer mark icon
     *
     * @return string
     */
    public function getVerifiedBuyerMark()
    {
        return $this->getViewFileUrl('Mageplaza_BetterProductReviews::media/verified-buyer.svg');
    }
}
