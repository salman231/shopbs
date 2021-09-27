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
use Webkul\Rmasystem\Model\ResourceModel\Shippinglabel\Collection;

/**
 * Rma shippingLabel CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingLabelRepository implements \Webkul\Rmasystem\Api\ShippingLabelRepositoryInterface
{
    /**
     * @var \Webkul\Rmasystem\Model\ShippinglabelFactory
     */
    protected $shippingLabelFactory;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ShippinglabelInterfaceFactory
     */
    protected $shippingLabelDataFactory;

    /**
     * @var \Webkul\Rmasystem\Model\ResourceModel\Shippinglabel
     */
    protected $shippingLabelResourceModel;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\Rmasystem\Api\Data\ShippinglabelSearchResultsInterfaceFactory
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
        \Webkul\Rmasystem\Model\ShippinglabelFactory $shippingLabelFactory,
        \Webkul\Rmasystem\Api\Data\ShippinglabelInterfaceFactory $shippingLabelDataFactory,
        \Webkul\Rmasystem\Model\ResourceModel\Shippinglabel $shippingLabelResourceModel,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Webkul\Rmasystem\Api\Data\ShippinglabelSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->shippingLabelFactory = $shippingLabelFactory;
        $this->shippingLabelDataFactory = $shippingLabelDataFactory;
        $this->shippingLabelResourceModel = $shippingLabelResourceModel;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Webkul\Rmasystem\Api\Data\ShippinglabelInterface $shippingLabel)
    {
        /** @var \Webkul\Rmasystem\Model\Shippinglabel $shippingLabelModel */
        $shippingLabelModel = null;
        if ($shippingLabel->getId() || (string)$shippingLabel->getId() === '0') {
            $shippingLabelModel = $this->shippingLabelFactory->create()->load($shippingLabel->getId());
            $groupDataAttributes = $this->dataObjectProcessor->buildOutputDataArray(
                $shippingLabel,
                \Webkul\Rmasystem\Api\Data\ShippinglabelInterface::class
            );
        } else {
            $shippingLabelModel = $this->shippingLabelFactory->create();
            $shippingLabelModel->setData($shippingLabel->getData());
        }
        try {
            $this->shippingLabelResourceModel->save($shippingLabelModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage() == (string)__('Could not save the record.')) {
                throw new InvalidTransitionException(__('Could not save the record.'));
            }
            throw $e;
        }

        $shippingLabelDataObject = $this->shippingLabelDataFactory->create()
            ->setData($shippingLabelModel->getData());
        return $shippingLabelDataObject;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($entityId)
    {
        $shippingLabel = $this->shippingLabelFactory->create();
        $this->shippingLabelResourceModel->load($shippingLabel, $entityId);
        
        return $shippingLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        /** @var \Webkul\Rmasystem\Model\ResourceModel\Shippinglabel\Collection $collection */
        $collection = $this->shippingLabelFactory->create()->getCollection();

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

        /** @var \Webkul\Rmasystem\Api\Data\ShippinglabelInterface[] $groups */
        $shippingLabels = [];
        /** @var \Webkul\Rmasystem\Model\Shippinglabel $shippingLabel */
        foreach ($collection as $shippingLabel) {
            /** @var \Magento\Rmasystem\Api\Data\ShippinglabelInterface $shippingLabelDataObject */
            $shippingLabelDataObject = $this->shippingLabelDataFactory->create()
                ->setData($shippingLabel->getData());
            $shippingLabels[] = $groupDataObject;
        }
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult->setItems($shippingLabels);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Webkul\Rmasystem\Api\Data\ShippinglabelInterface $shippingLabel)
    {
        try {
            $this->shippingLabelResourceModel->delete($shippingLabel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($shippingLabelId)
    {
        return $this->delete($this->getById($shippingLabelId));
    }
}
