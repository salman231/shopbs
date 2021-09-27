<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
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
use Webkul\Rmasystem\Model\ResourceModel\Reason\Collection;

/**
 * Rma reason CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReasonRepository implements \Webkul\Rmasystem\Api\ReasonRepositoryInterface
{
    /**
     * @var \Webkul\Rmasystem\Model\ReasonFactory
     */
    protected $reasonFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ReasonInterfaceFactory
     */
    protected $reasonDataFactory;

    /**
     * @var \Webkul\Rmasystem\Model\ResourceModel\Reason
     */
    protected $reasonResourceModel;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ReasonSearchResultsInterfaceFactory
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
        \Webkul\Rmasystem\Model\ReasonFactory $reasonFactory,
        \Webkul\Rmasystem\Api\Data\ReasonInterfaceFactory $reasonDataFactory,
        \Webkul\Rmasystem\Model\ResourceModel\Reason $reasonResourceModel,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Webkul\Rmasystem\Api\Data\ReasonSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->reasonFactory = $reasonFactory;
        $this->reasonDataFactory = $reasonDataFactory;
        $this->reasonResourceModel = $reasonResourceModel;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Webkul\Rmasystem\Api\Data\ReasonInterface $reason)
    {
        /** @var \Webkul\Rmasystem\Model\Reason $reasonModel */
        $reasonModel = null;
        if ($reason->getId() || (string)$reason->getId() === '0') {
            $groupDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
                $reason,
                \Webkul\Rmasystem\Api\Data\ReasonInterface::class
            );
            $reasonModel = $reason;
            $reasonModel->setData($groupDataAttributes);
        } else {
            $reasonModel = $this->reasonFactory->create();
            $reasonModel->setData($reason->getData());
        }
        try {
            $this->reasonResourceModel->save($reasonModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage() == (string)__('Could not save the record.')) {
                throw new InvalidTransitionException(__('Could not save the record.'));
            }
            throw $e;
        }

        $reasonDataObject = $this->reasonDataFactory->create()
            ->setData($reasonModel->getData());
        return $reasonDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($entityId)
    {
        $reason = $this->reasonFactory->create();
        $this->reasonResourceModel->load($reason, $entityId);
        
        return $reason;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        /** @var \Webkul\Rmasystem\Model\ResourceModel\Reason\Collection $collection */
        $collection = $this->reasonFactory->create()->getCollection();

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

        /** @var \Webkul\Rmasystem\Api\Data\ReasonInterface[] $groups */
        $reasons = [];
        /** @var \Webkul\Rmasystem\Model\Reason $reason */
        foreach ($collection as $reason) {
            /** @var \Magento\Rmasystem\Api\Data\ReasonInterface $reasonDataObject */
            $reasonDataObject = $this->reasonDataFactory->create()
                ->setData($reason->getData());
            $reasons[] = $groupDataObject;
        }
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult->setItems($reasons);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Webkul\Rmasystem\Api\Data\ReasonInterface $reason)
    {
        try {
            $this->reasonResourceModel->delete($reason);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($reasonId)
    {
        return $this->delete($this->getById($reasonId));
    }
}
