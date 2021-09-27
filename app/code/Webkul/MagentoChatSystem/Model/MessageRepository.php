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

use Webkul\MagentoChatSystem\Api\Data;
use Webkul\MagentoChatSystem\Api\MessageRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MagentoChatSystem\Model\ResourceModel\Message as ResourceMessage;
use Webkul\MagentoChatSystem\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\MagentoChatSystem\Api\Data\MessageInterface;

/**
 * Class MessageRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageRepository implements MessageRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $timeSlotConfigFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $messageCollectionFactory;

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
    protected $messageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceMessage $resource
     * @param MessageFactory $messageFactory
     * @param MessageCollectionFactory $messageCollectionFactory
     * @param Data\MessageSearchResultInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceMessage $resource,
        MessageFactory $messageFactory,
        MessageCollectionFactory $messageCollectionFactory,
        Data\MessageSearchResultInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->messageFactory = $messageFactory;
        $this->messageCollectionFactory = $messageCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->messageFactory = $messageFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Customer data
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\MessageInterface $message
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\MessageInterface $message)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $message->setStoreId($storeId);
        try {
            $this->resource->save($message);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $message;
    }

    /**
     * Load Preorder Complete data by given Block Identity
     *
     * @param string $id
     * @return PreorderComplete
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $message = $this->messageFactory->create();
        $this->resource->load($message, $id);
        if (!$message->getEntityId()) {
            throw new NoSuchEntityException(__('Customer with id "%1" does not exist.', $id));
        }
        return $message;
    }

    /**
     * Load Message data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Webkul\MagentoChatSystem\Model\ResourceModel\Message\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->messageCollectionFactory->create();
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
        $timeSlotData = [];
        /** @var PreorderComplete $timeSlotData */
        foreach ($collection as $messageModel) {
            $timeSlot = $this->messageFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $timeSlot,
                $timeSlotData->getData(),
                MessageInterface::class
            );
            $timeSlotData[] = $this->dataObjectProcessor->buildOutputDataArray(
                $timeSlot,
                MessageInterface::class
            );
        }
        $searchResults->setItems($timeSlotData);
        return $searchResults;
    }

    /**
     * Delete Message
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\MessageInterface $message
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\MessageInterface $message)
    {
        try {
            $this->resource->delete($message);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Message by given Id
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
