<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Webkul\MagentoChatSystem\Api\AgentRatingRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentRating as ResourceAgentRating;
use Webkul\MagentoChatSystem\Api\Data\AgentRatingSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentRating\CollectionFactory as AgentRatingCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;

class AgentRatingRepository implements AgentRatingRepositoryInterface
{
    /**
     * @var AgentRatingFactory
     */
    protected $agentRatingFactory;

    /**
     * @var AgentRatingCollectionFactory
     */
    protected $agentRatingCollectionFactory;

    /**
     * @var ResourceAgentRating
     */
    protected $resource;

    /**
     * @var AgentRatingSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @param ResourceAgentRating $resource
     * @param AgentRatingFactory $agentRatingFactory
     * @param AgentRatingCollectionFactory $agentRatingCollectionFactory
     * @param AgentRatingSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceAgentRating $resource,
        AgentRatingFactory $agentRatingFactory,
        AgentRatingCollectionFactory $agentRatingCollectionFactory,
        AgentRatingSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->agentRatingFactory = $agentRatingFactory;
        $this->agentRatingCollectionFactory = $agentRatingCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface $agentRating
    ) {
        try {
            $this->resource->save($agentRating);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the agentRating: %1',
                $exception->getMessage()
            ));
        }
        return $agentRating;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($agentRatingId)
    {
        $agentRating = $this->agentRatingFactory->create();
        $this->resource->load($agentRating, $agentRatingId);
        if (!$agentRating->getId()) {
            throw new NoSuchEntityException(__('AgentRating with id "%1" does not exist.', $agentRatingId));
        }
        return $agentRating;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->agentRatingCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface $agentRating
    ) {
        try {
            $this->resource->delete($agentRating);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the AgentRating: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($agentRatingId)
    {
        return $this->delete($this->getById($agentRatingId));
    }
}
