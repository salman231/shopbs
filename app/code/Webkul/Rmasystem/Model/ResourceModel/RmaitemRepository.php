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
use Webkul\Rmasystem\Model\ResourceModel\Rmaitem\Collection;

/**
 * Rma rmaItem CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RmaitemRepository implements \Webkul\Rmasystem\Api\RmaitemRepositoryInterface
{
    /**
     * @var \Webkul\Rmasystem\Model\RmaitemFactory
     */
    protected $rmaItemFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\RmaitemInterfaceFactory
     */
    protected $rmaItemDataFactory;

    /**
     * @var \Webkul\Rmasystem\Model\ResourceModel\Rmaitem
     */
    protected $rmaItemResourceModel;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\Rmasystem\Api\Data\RmaitemSearchResultsInterfaceFactory
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
        \Webkul\Rmasystem\Model\RmaitemFactory $rmaItemFactory,
        \Webkul\Rmasystem\Api\Data\RmaitemInterfaceFactory $rmaItemDataFactory,
        \Webkul\Rmasystem\Model\ResourceModel\Rmaitem $rmaItemResourceModel,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Webkul\Rmasystem\Api\Data\RmaitemSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->rmaItemFactory = $rmaItemFactory;
        $this->rmaItemDataFactory = $rmaItemDataFactory;
        $this->rmaItemResourceModel = $rmaItemResourceModel;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Webkul\Rmasystem\Api\Data\RmaitemInterface $rmaItem)
    {
        /** @var \Webkul\Rmasystem\Model\Rmaitem $rmaItemModel */
        $rmaItemModel = null;
        if ($rmaItem->getId() || (string)$rmaItem->getId() === '0') {
            $rmaItemModel = $this->rmaItemFactory->create()->load($rmaItem->getId());
            $groupDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
                $rmaItem,
                \Webkul\Rmasystem\Api\Data\RmaitemInterface::class
            );
        } else {
            $rmaItemModel = $this->rmaItemFactory->create();
            $rmaItemModel->setData($rmaItem->getData());
        }
        try {
            $this->rmaItemResourceModel->save($rmaItemModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage() == (string)__('Could not save the record.')) {
                throw new InvalidTransitionException(__('Could not save the record.'));
            }
            throw $e;
        }

        $rmaItemDataObject = $this->rmaItemDataFactory->create()
            ->setData($rmaItemModel->getData());
        return $rmaItemDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($entityId)
    {
        $rmaItem = $this->rmaItemFactory->create();
        $this->rmaItemResourceModel->load($rmaItem, $entityId);
        if (!$rmaItem->getId()) {
            throw new NoSuchEntityException(__('Record with id "%1" does not exist.', $entityId));
        }
        return $rmaItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        /** @var \Webkul\Rmasystem\Model\ResourceModel\Rmaitem\Collection $collection */
        $collection = $this->rmaItemFactory->create()->getCollection();

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

        /** @var \Webkul\Rmasystem\Api\Data\RmaitemInterface[] $groups */
        $rmaItems = [];
        /** @var \Webkul\Rmasystem\Model\Rmaitem $rmaItem */
        foreach ($collection as $rmaItem) {
            /** @var \Magento\Rmasystem\Api\Data\RmaitemInterface $rmaItemDataObject */
            $rmaItemDataObject = $this->rmaItemDataFactory->create()
                ->setData($rmaItem->getData());
            $rmaItems[] = $groupDataObject;
        }
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult->setItems($rmaItems);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Webkul\Rmasystem\Api\Data\RmaitemInterface $rmaItem)
    {
        try {
            $this->rmaItemResourceModel->delete($rmaItem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($rmaItemId)
    {
        return $this->delete($this->getById($rmaItemId));
    }
}
