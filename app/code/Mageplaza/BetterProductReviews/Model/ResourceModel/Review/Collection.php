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

namespace Mageplaza\BetterProductReviews\Model\ResourceModel\Review;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Review\Helper\Data;
use Magento\Review\Model\Rating\Option\VoteFactory;
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BetterProductReviews\Api\Data\ProductReviewsSearchResultInterface;
use Mageplaza\BetterProductReviews\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

/**
 * Class Collection
 *
 * @package Mageplaza\BetterProductReviews\Model\ResourceModel\Review
 */
class Collection extends ReviewCollection implements ProductReviewsSearchResultInterface
{
    /**
     * Rating option vote table
     *
     * @var string
     */
    protected $_ratingOptVoteTable = null;

    /**
     * Rating store table
     *
     * @var string
     */
    protected $_ratingStoreTable = null;

    /**
     * Review reply table
     *
     * @var null
     */
    protected $_reviewReplyTable = null;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * Collection constructor.
     *
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Data $reviewData
     * @param VoteFactory $voteFactory
     * @param StoreManagerInterface $storeManager
     * @param HelperData $helperData
     * @param SearchCriteriaInterface $searchCriteria
     * @param null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Data $reviewData,
        VoteFactory $voteFactory,
        StoreManagerInterface $storeManager,
        HelperData $helperData,
        SearchCriteriaInterface $searchCriteria,
        $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_helperData = $helperData;
        $this->searchCriteria = $searchCriteria;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $reviewData,
            $voteFactory,
            $storeManager,
            $connection,
            $resource
        );
    }

    /**
     * Initialize select
     *
     * @return $this|ReviewCollection
     */
    protected function _initSelect()
    {
        if ($this->_helperData->isEnabled()) {
            $this->getSelect()->from(['main_table' => $this->getMainTable()]);

            return $this;
        }

        return parent::_initSelect();
    }

    /**
     * @return $this
     */
    public function addReviewDetail()
    {
        $this->getSelect()->join(
            ['detail' => $this->getReviewDetailTable()],
            'main_table.review_id = detail.review_id',
            ['detail_id', 'title', 'detail', 'nickname', 'customer_id']
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addReviewDetailTable()
    {
        $this->getSelect()->join(
            ['detail' => $this->getReviewDetailTable()],
            'main_table.review_id = detail.review_id',
            [
                'detail_id' => new Zend_Db_Expr('MAX(detail.detail_id)'),
                'title' => new Zend_Db_Expr('MAX(detail.title)'),
                'detail' => new Zend_Db_Expr('MAX(detail.detail)'),
                'nickname' => new Zend_Db_Expr('MAX(detail.nickname)'),
                'customer_id' => new Zend_Db_Expr('MAX(detail.customer_id)'),
                'mp_bpr_images' => new Zend_Db_Expr('MAX(detail.mp_bpr_images)'),
                'mp_bpr_recommended_product' => new Zend_Db_Expr('MAX(detail.mp_bpr_recommended_product)'),
                'mp_bpr_verified_buyer' => new Zend_Db_Expr('MAX(detail.mp_bpr_verified_buyer)'),
                'mp_bpr_helpful' => new Zend_Db_Expr('MAX(detail.mp_bpr_helpful)')
            ]
        );

        return $this;
    }

    /**
     * Join left with mageplaza review reply table
     *
     * @return $this
     */
    public function addReviewReplyTable()
    {
        $this->getSelect()->joinLeft(
            ['reply' => $this->getReviewReplyTable()],
            'main_table.review_id = reply.review_id',
            [
                'reply_enabled' => new Zend_Db_Expr('MAX(reply.reply_enabled)'),
                'reply_nickname' => new Zend_Db_Expr('MAX(reply.reply_nickname)'),
                'reply_content' => new Zend_Db_Expr('MAX(reply.reply_content)'),
                'reply_created_at' => new Zend_Db_Expr('MAX(reply.reply_created_at)')
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addAverageVotingTable()
    {
        $this->getSelect()->joinLeft(
            ['voting' => $this->getRatingOptionVoteTable()],
            'main_table.review_id = voting.review_id',
            ['avg_value' => new Zend_Db_Expr('AVG(voting.value)')]
        )->group(['main_table.review_id']);

        return $this;
    }

    /**
     * @param string $storeId
     *
     * @return $this
     */
    public function addVotingTable($storeId)
    {
        $this->getSelect()->joinLeft(
            ['voting' => $this->getRatingOptionVoteTable()],
            'main_table.review_id = voting.review_id',
            ['voting.value']
        )->joinLeft(
            ['voting_store' => $this->getTable('rating_store')],
            'voting.rating_id = voting_store.rating_id'
        )->where('voting_store.store_id = ?', $storeId);

        return $this;
    }

    /**
     * @param string $voteValue
     * @param string $storeId
     *
     * @return $this
     */
    public function addVotingFilter($voteValue, $storeId)
    {
        $this->addVotingTable($storeId)->getSelect()
            ->join(
                ['vote_store' => $this->getRatingStoreTable()],
                'voting.rating_id = vote_store.rating_id'
            )
            ->where("voting.value = '" . $voteValue . "' AND vote_store.store_id = '" . $storeId . "'");

        return $this;
    }

    /**
     * Get rating option vote table
     *
     * @return string
     */
    protected function getRatingOptionVoteTable()
    {
        if ($this->_ratingOptVoteTable === null) {
            $this->_ratingOptVoteTable = $this->getTable('rating_option_vote');
        }

        return $this->_ratingOptVoteTable;
    }

    /**
     * Get rating store table
     *
     * @return string
     */
    protected function getRatingStoreTable()
    {
        if ($this->_ratingStoreTable === null) {
            $this->_ratingStoreTable = $this->getTable('rating_store');
        }

        return $this->_ratingStoreTable;
    }

    /**
     * Get review reply table
     *
     * @return string
     */
    protected function getReviewReplyTable()
    {
        if ($this->_reviewReplyTable === null) {
            $this->_reviewReplyTable = $this->getTable('mageplaza_betterproductreviews_review_reply');
        }

        return $this->_reviewReplyTable;
    }

    /**
     * Set voting value order
     *
     * @param string $dir
     *
     * @return $this
     */
    public function setVotingOrder($dir = 'DESC')
    {
        $this->setOrder('voting.value', $dir);

        return $this;
    }

    /**
     * Set helpfulness order
     *
     * @param string $dir
     *
     * @return $this
     */
    public function setHelpfulnessOrder($dir = 'DESC')
    {
        $this->setOrder('mp_bpr_helpful', $dir);

        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     */
    public function setLimitCollection($limit, $offset)
    {
        $this->getSelect()->limit($limit, $offset);
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        $this->searchCriteria = $searchCriteria;

        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * @param array|null $items
     *
     * @return $this|ProductReviewsSearchResultInterface
     * @throws Exception
     */
    public function setItems(array $items = null)
    {
        if (!$items) {
            return $this;
        }
        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }
}
