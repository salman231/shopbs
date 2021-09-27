<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Model\ResourceModel;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Webkul\Rmasystem\Model\ResourceModel\Conversation\Collection;

/**
 * Rma conversation CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConversationRepository implements \Webkul\Rmasystem\Api\ConversationRepositoryInterface
{
    /**
     * @var \Webkul\Rmasystem\Model\ConversationFactory
     */
    protected $conversationFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory
     */
    protected $conversationDataFactory;

    /**
     * @var \Webkul\Rmasystem\Model\ResourceModel\Conversation
     */
    protected $conversationResourceModel;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ConversationSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Customer\Api\Data\GroupInterfaceFactory $groupDataFactory
     * @param \Magento\Customer\Model\ResourceModel\Group $groupResourceModel
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Customer\Api\Data\GroupSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassRepositoryInterface
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        \Webkul\Rmasystem\Model\ConversationFactory $conversationFactory,
        \Webkul\Rmasystem\Api\Data\ConversationInterfaceFactory $conversationDataFactory,
        \Webkul\Rmasystem\Model\ResourceModel\Conversation $conversationResourceModel,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Webkul\Rmasystem\Api\Data\ConversationSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->conversationFactory = $conversationFactory;
        $this->conversationDataFactory = $conversationDataFactory;
        $this->conversationResourceModel = $conversationResourceModel;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Webkul\Rmasystem\Api\Data\ConversationInterface $conversation)
    {
        /** @var \Webkul\Rmasystem\Model\Conversation $conversationModel */
        $conversationModel = null;
        if ($conversation->getId() || (string)$conversation->getId() === '0') {
            $conversationModel = $this->conversationFactory->create()->load($conversation->getId());
            $groupDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
                $conversation,
                \Webkul\Rmasystem\Api\Data\ConversationInterface::class
            );
        } else {
            $conversationModel = $this->conversationFactory->create();
            $conversationModel->setData($conversation->getData());
        }
        try {
            $this->conversationResourceModel->save($conversationModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage() == (string)__('Could not save the record.')) {
                throw new InvalidTransitionException(__('Could not save the record.'));
            }
            throw $e;
        }

        $conversationDataObject = $this->conversationDataFactory->create()
            ->setData($conversationModel->getData());
        return $conversationDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($entityId)
    {
        $conversation = $this->conversationFactory->create();
        $this->conversationResourceModel->load($conversation, $entityId);
        if (!$conversation->getId()) {
            throw new NoSuchEntityException(__('Record with id "%1" does not exist.', $entityId));
        }
        return $conversation;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        /** @var \Webkul\Rmasystem\Model\ResourceModel\Conversation\Collection $collection */
        $collection = $this->conversationFactory->create()->getCollection();

        /** @var FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType(): 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Webkul\Rmasystem\Api\Data\ConversationInterface[] $groups */
        $conversations = [];
        /** @var \Webkul\Rmasystem\Model\Conversation $conversation */
        foreach ($collection as $conversation) {
            /** @var \Magento\Rmasystem\Api\Data\ConversationInterface $conversationDataObject */
            $conversationDataObject = $this->conversationDataFactory->create()
                ->setData($conversation->getData());
            $conversations[] = $groupDataObject;
        }
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult->setItems($conversations);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Webkul\Rmasystem\Api\Data\ConversationInterface $conversation)
    {
        try {
            $this->conversationResourceModel->delete($conversation);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($conversationId)
    {
        return $this->delete($this->getById($conversationId));
    }
}
