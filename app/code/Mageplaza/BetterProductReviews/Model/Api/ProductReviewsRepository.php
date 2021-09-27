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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Model\Api;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Review\Model\Rating\Option\Vote;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Mageplaza\BetterProductReviews\Api\ProductReviewsRepositoryInterface;
use Mageplaza\BetterProductReviews\Helper\Data;
use Mageplaza\BetterProductReviews\Model\ResourceModel\Review\Collection;
use Mageplaza\BetterProductReviews\Model\ResourceModel\Review\CollectionFactory;

/**
 * Class ProductReviewsRepository
 * @package Mageplaza\BetterProductReviews\Model\Api
 */
class ProductReviewsRepository implements ProductReviewsRepositoryInterface
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var CollectionFactory
     */
    protected $reviewCollection;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var ReviewFactory
     */
    protected $_review;

    /**
     * @var RatingFactory
     */
    protected $_rating;

    /**
     * @var Vote
     */
    protected $vote;

    /**
     * BlogRepository constructor.
     *
     * @param Data $helperData
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param CollectionProcessorInterface $collectionProcessor
     * @param RequestInterface $request
     * @param CollectionFactory $reviewCollection
     * @param Product $product
     * @param Vote $vote
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param DateTime $date
     */
    public function __construct(
        Data $helperData,
        CustomerRepositoryInterface $customerRepositoryInterface,
        CollectionProcessorInterface $collectionProcessor,
        RequestInterface $request,
        CollectionFactory $reviewCollection,
        Product $product,
        Vote $vote,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        DateTime $date
    ) {
        $this->_request = $request;
        $this->_helperData = $helperData;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->date = $date;
        $this->collectionProcessor = $collectionProcessor;
        $this->reviewCollection = $reviewCollection;
        $this->_product = $product;
        $this->_review = $reviewFactory;
        $this->_rating = $ratingFactory;
        $this->vote = $vote;
    }

    /**
     * @inheritDoc
     */
    public function getReviewById($reviewId)
    {
        $collection = $this->getReviewCollection();
        $collection->addFieldToFilter('main_table.review_id', $reviewId);

        return $this->getAllItem($collection);
    }

    /**
     * @inheritDoc
     */
    public function getReviewByProductId($productId)
    {
        $collection = $this->getReviewCollection();
        $collection->addFieldToFilter('main_table.entity_pk_value', $productId);

        return $this->getAllItem($collection);
    }

    /**
     * @inheritDoc
     */
    public function getReviewByProductSku($productSku)
    {
        $product = $this->_product->loadByAttribute('sku', $productSku);
        if ($product) {
            $collection = $this->getReviewCollection();
            $collection->addFieldToFilter('main_table.entity_pk_value', $product->getId());

            return $this->getAllItem($collection);
        }

        return 'No element found matching the given condition.';
    }

    /**
     * @inheritDoc
     */
    public function getAllReviews()
    {
        $collection = $this->getReviewCollection();

        return $this->getAllItem($collection);
    }

    /**
     * @inheritDoc
     */
    public function getListReviews(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->getReviewCollection();

        return $this->getListEntity($collection, $searchCriteria);
    }

    /**
     * @inheritDoc
     */
    public function getReviewByCustomerId($customerId)
    {
        $collection = $this->getReviewCollection();
        if ($customerId === 0) {
            $collection->addFieldToFilter('detail.customer_id', ['null' => true]);
        } else {
            $collection->addFieldToFilter('detail.customer_id', $customerId);
        }

        return $this->getAllItem($collection);
    }

    /**
     * @inheritDoc
     */
    public function getProductByReviewId($reviewId)
    {
        $reviewCollection = $this->getReviewCollection();
        $productId = $reviewCollection->addFieldToFilter('main_table.review_id', $reviewId)->getFirstItem()
            ->getData('entity_pk_value');

        return $this->_product->load($productId);
    }

    /**
     * @inheritDoc
     */
    public function addReview($productId, $review)
    {
        $data = $review->getData();
        $storeId = isset($data['store_id']) ? $data['store_id'] : 1;
        $customerId = isset($data['customer_id']) ? $data['customer_id'] : null;
        $avgValue = isset($data['avg_value']) ? $data['avg_value'] : '5';
        $status = isset($data['status_id']) ? $data['status_id'] : Review::STATUS_PENDING;
        $ratings = $this->getRatingCollection($storeId);
        $object = $this->_review->create()->setData($data);
        $object->unsetData('review_id');
        $product = $this->_product->load($productId);

        if (!$product->getId()) {
            throw new Exception(__('Product does not exist.'));
        }

        if ($object->validate()) {
            $object->setEntityId($object->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                ->setEntityPkValue($productId)
                ->setStatusId($status)
                ->setCustomerId($customerId)
                ->setStoreId($storeId)
                ->setStores([$storeId])
                ->save();
            foreach ($ratings as $ratingId => $rating) {
                foreach ($rating->getOptions() as $option) {
                    if ($option->getValue() === $avgValue) {
                        $this->_rating->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($object->getId())
                            ->setCustomerId($customerId)
                            ->addOptionVote($option->getId(), $productId);
                    }
                }
            }
            $object->aggregate();

            return $object;
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function updateReview($reviewId, $review)
    {
        $data = $review->getData();
        unset($data['review_id']);

        $object = $this->_review->create()->load($reviewId);
        if (!$object->getId()) {
            throw new Exception(__('Review does not exist.'));
        }

        $storeId = isset($data['store_id']) ? $data['store_id'] : $object->getData('store_id');
        $customerId = isset($data['customer_id']) ? $data['customer_id'] : $object->getData('customer_id');
        $ratings = $this->getRatingCollection($storeId);
        $object->addData($data);
        if ($object->validate()) {
            $object->save();
            $votes = $this->vote->getResourceCollection()->setReviewFilter($reviewId)->addOptionInfo()->load()
                ->addRatingOptions();
            if (isset($data['avg_value'])) {
                foreach ($ratings as $ratingId => $rating) {
                    foreach ($rating->getOptions() as $option) {
                        if ($option->getValue() === $data['avg_value']) {
                            $vote = $votes->getItemByColumnValue('rating_id', $ratingId);
                            $this->_rating->create()
                                ->setVoteId($vote->getId())
                                ->setReviewId($reviewId)
                                ->updateOptionVote($option->getId());
                        }
                    }
                }
            }
            $object->aggregate();

            return $object;
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function updateMultiReview($statusId, $reviewIds)
    {
        $idUpdated = [];
        $reviewIds = explode(',', $reviewIds);
        $reviewCollection = $this->getReviewCollection();
        $reviewCollection->addFieldToFilter('status_id', ['neq' => $statusId]);
        if ($reviewCollection->count() < 1) {
            return __('No review to updated.')->render();
        }
        foreach ($reviewCollection->getItems() as $review) {
            if (!empty($reviewIds) && !in_array($review->getId(), $reviewIds, true)) {
                continue;
            } else {
                $idUpdated[] = $review->getId();
                $review->setStatusId($statusId)->save();
            }
        }

        return __('The review has id is %1 has been updated', implode(',', $idUpdated))->render();
    }

    /**
     * @inheritDoc
     */
    public function deleteReview($reviewId)
    {
        $review = $this->_review->create()->load($reviewId);
        if (!$review->getId()) {
            throw new Exception(__('Review does not exist.'));
        }

        $review->aggregate()->delete();

        return true;
    }

    /**
     * @param $storeId
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getRatingCollection($storeId)
    {
        return $this->_rating->create()->getResourceCollection()->addEntityFilter(
            'product'
        )->setPositionOrder()->addRatingPerStoreName(
            $storeId
        )->setStoreFilter(
            $storeId
        )->setActiveFilter(
            true
        )->load()->addOptionToItems();
    }

    /**
     * @return Collection
     */
    public function getReviewCollection()
    {
        return $this->reviewCollection->create()->addReviewDetailTable()->addAverageVotingTable();
    }

    /**
     * @param Collection $searchResult
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return mixed
     */
    protected function getListEntity($searchResult, $searchCriteria)
    {
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult->getItems();
    }

    /**
     * @param AbstractCollection $collection
     *
     * @return mixed
     * @throws Exception
     */
    protected function getAllItem($collection)
    {
        $page = $this->_request->getParam('page', 1);
        $limit = $this->_request->getParam('limit', 10);

        $collection->getSelect()->limitPage($page, $limit);
        if ($collection->count() < 1) {
            throw new Exception(__('No element found matching the given condition.'));
        }

        return $collection->getItems();
    }
}
