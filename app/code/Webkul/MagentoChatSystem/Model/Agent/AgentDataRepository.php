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
namespace Webkul\MagentoChatSystem\Model\Agent;

use Webkul\MagentoChatSystem\Api\Data;
use Webkul\MagentoChatSystem\Api\AgentDataRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentData as ResourceData;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\CollectionFactory as AgentDataCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\MagentoChatSystem\Api\Data\AgentDataInterface;

/**
 * Class AgentDataRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AgentDataRepository implements AgentDataRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockCollectionFactory
     */
    protected $agentCollectionFactory;

    /**
     * @var Data\BlockSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\MagentoChatSystem\Api\Data\MessageInterfaceFactory
     */
    protected $agentDataFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceData $resource
     * @param \Webkul\MagentoChatSystem\Model\AgentDataFactory $agentDataFactory
     * @param AgentDataCollectionFactory $agentCollectionFactory
     * @param Data\AgentDataSearchResultInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceData $resource,
        \Webkul\MagentoChatSystem\Model\AgentDataFactory $agentDataFactory,
        AgentDataCollectionFactory $agentCollectionFactory,
        Data\AgentDataSearchResultInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->agentDataFactory = $agentDataFactory;
        $this->agentCollectionFactory = $agentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Agent data
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface $agent
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\AgentDataInterface $agent)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $agent->setStoreId($storeId);
        try {
            $this->resource->save($agent);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $agent;
    }

    /**
     * Load Agent data by given Block Identity
     *
     * @param string $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $agent = $this->agentDataFactory->create();
        $this->resource->load($agent, $id);
        if (!$agent->getEntityId()) {
            throw new NoSuchEntityException(__('Agent with id "%1" does not exist.', $id));
        }
        return $agent;
    }

    /**
     *
     * @param string $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByAgentId($id)
    {
        $agent = $this->agentDataFactory->create();
        $agent->load($id, 'agent_id');
        return $agent;
    }

    /**
     *
     * @param string $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByUniqueId($id)
    {
        $agent = $this->agentDataFactory->create();
        $agent->load($id, 'agent_unique_id');
        if (!$agent->getEntityId()) {
            throw new NoSuchEntityException(__('Agent with unique id "%1" does not exist.', $id));
        }
        return $agent;
    }

    /**
     * Load Agent data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Webkul\MagentoChatSystem\Model\ResourceModel\AgentData\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->agentCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $agentData = [];
        foreach ($collection as $agentModel) {
            $agent = $this->agentDataFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $agent,
                $agentModel->getData(),
                AgentDataInterface::class
            );
            $agentData[] = $this->dataObjectProcessor->buildOutputDataArray(
                $agent,
                AgentDataInterface::class
            );
        }
        $searchResults->setItems($agentData);
        return $searchResults;
    }

    /**
     * Delete Agent
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface $agent
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\AgentDataInterface $agent)
    {
        try {
            $this->resource->delete($agent);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Agent by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
