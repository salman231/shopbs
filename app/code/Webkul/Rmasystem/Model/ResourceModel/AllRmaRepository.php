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
use Webkul\Rmasystem\Model\ResourceModel\Allrma\Collection;

/**
 * Rma rma CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AllRmaRepository implements \Webkul\Rmasystem\Api\AllRmaRepositoryInterface
{
    /**
     * @var \Webkul\Rmasystem\Model\AllrmaFactory
     */
    protected $rmaFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\AllrmaInterfaceFactory
     */
    protected $rmaDataFactory;

    /**
     * @var \Webkul\Rmasystem\Model\ResourceModel\Allrma
     */
    protected $rmaResourceModel;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\Rmasystem\Api\Data\AllrmaSearchResultsInterfaceFactory
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
        \Webkul\Rmasystem\Model\AllrmaFactory $rmaFactory,
        \Webkul\Rmasystem\Api\Data\AllrmaInterfaceFactory $rmaDataFactory,
        \Webkul\Rmasystem\Model\ResourceModel\Allrma $rmaResourceModel,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Webkul\Rmasystem\Api\Data\AllrmaSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->rmaFactory = $rmaFactory;
        $this->rmaDataFactory = $rmaDataFactory;
        $this->rmaResourceModel = $rmaResourceModel;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Webkul\Rmasystem\Api\Data\AllrmaInterface $rma)
    {
        /** @var \Webkul\Rmasystem\Model\Allrma $rmaModel */
        $rmaModel = null;
        if ($rma->getId() || (string)$rma->getId() === '0') {
            $rmaModel = $this->rmaFactory->create()->load($rma->getId());
            $rmaModel->setData($rma->getData());
        } else {
            $rmaModel = $this->rmaFactory->create();
            $rmaModel->setData($rma->getData());
        }
        try {
            $this->rmaResourceModel->save($rmaModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage() == (string)__('Could not save the record.')) {
                throw new InvalidTransitionException(__('Could not save the record.'));
            }
            throw $e;
        }

        $rmaDataObject = $this->rmaDataFactory->create()
            ->setData($rmaModel->getData());
        return $rmaDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($entityId)
    {
        $rma = $this->rmaFactory->create();
        $this->rmaResourceModel->load($rma, $entityId);
        if (!$rma->getId()) {
            throw new NoSuchEntityException(__('Record with id "%1" does not exist.', $entityId));
        }
        return $rma;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        /** @var \Webkul\Rmasystem\Model\ResourceModel\Allrma\Collection $collection */
        $collection = $this->rmaFactory->create()->getCollection();

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

        /** @var \Webkul\Rmasystem\Api\Data\AllrmaInterface[] $groups */
        $rmas = [];
        /** @var \Webkul\Rmasystem\Model\Allrma $rma */
        foreach ($collection as $rma) {
            /** @var \Magento\Rmasystem\Api\Data\AllrmaInterface $rmaDataObject */
            $rmaDataObject = $this->rmaDataFactory->create()
                ->setData($rma->getData());
            $rmas[] = $groupDataObject;
        }
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult->setItems($rmas);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Webkul\Rmasystem\Api\Data\AllrmaInterface $rma)
    {
        try {
            $this->rmaResourceModel->delete($rma);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($rmaId)
    {
        return $this->delete($this->getById($rmaId));
    }
}
