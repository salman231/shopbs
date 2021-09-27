<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Model;

use Amasty\Customform\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Amasty\Customform\Model\ResourceModel\Answer as AnswerResource;
use Amasty\Customform\Model\AnswerFactory;
use Amasty\Customform\Model\ResourceModel\Answer\CollectionFactory as AnswerCollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;


class AnswerRepository implements \Amasty\Customform\Api\AnswerRepositoryInterface
{
    /**
     * @var array
     */
    protected $answer = [];

    /**
     * @var ResourceModel\Answer
     */
    private $answerResource;

    /**
     * @var AnswerFactory
     */
    private $answerFactory;

    /**
     * @var ResourceModel\Answer\CollectionFactory
     */
    private $answerCollectionFactory;

    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        AnswerResource $answerResource,
        AnswerFactory $answerFactory,
        AnswerCollectionFactory $answerCollectionFactory,
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->answerResource = $answerResource;
        $this->answerFactory = $answerFactory;
        $this->answerCollectionFactory = $answerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\AnswerInterface $answer)
    {
        if ($answer->getAnswerId()) {
            $answer = $this->get($answer->getAnswerId())->addData($answer->getData());
        }

        try {
            $this->answerResource->save($answer);
            unset($this->answer[$answer->getAnswerId()]);
        } catch (\Exception $e) {
            if ($answer->getAnswerId()) {
                throw new CouldNotSaveException(
                    __('Unable to save answer with ID %1. Error: %2', [$answer->getAnswerId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new answer. Error: %1', $e->getMessage()));
        }

        return $answer;
    }

    /**
     * {@inheritdoc}
     */
    public function get($answerId)
    {
        if (!isset($this->answer[$answerId])) {
            /** @var \Amasty\Customform\Model\Answer $answer */
            $answer = $this->answerFactory->create();
            $this->answerResource->load($answer, $answerId);
            if (!$answer->getAnswerId()) {
                throw new NoSuchEntityException(__('Answer with specified ID "%1" was not found.', $answerId));
            }
            $this->answer[$answerId] = $answer;
        }
        return $this->answer[$answerId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\AnswerInterface $answer)
    {
        try {
            $this->answerResource->delete($answer);
            unset($this->answer[$answer->getAnswerId()]);
        } catch (\Exception $e) {
            if ($answer->getAnswerId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove answer with ID %1. Error: %2', [$answer->getAnswerId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove answer. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($answerId)
    {
        $model = $this->get($answerId);
        $this->delete($model);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getListFilter(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->answerCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $collection);
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $answerList = [];
        foreach ($collection->getItems() as $answer) {
            $answerList[] = $answer;
        }

        return $answerList;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param  $collection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, $collection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $answerCollection = $this->answerCollectionFactory->create();
        $answerList = [];

        foreach ($answerCollection as $answer) {
            $answerList[] = $answer;
        }

        return $answerList;
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param ResourceModel\Answer\Collection  $collection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, ResourceModel\Answer\Collection $collection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
