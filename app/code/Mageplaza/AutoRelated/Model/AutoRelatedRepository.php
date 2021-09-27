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
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Model;

use Magento\Eav\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\AutoRelated\Api\AutoRelatedRepositoryInterface;
use Mageplaza\AutoRelated\Helper\Data;
use Mageplaza\AutoRelated\Model\Config\Source\Type;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule\Collection;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule\CollectionFactory;

/**
 * Class AutoRelatedRepository
 * @package Mageplaza\AutoRelated\Model
 */
class AutoRelatedRepository implements AutoRelatedRepositoryInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var FilterProcessor
     */
    protected $productCollectionProcessor;

    /**
     * AutoRelatedRepository constructor.
     *
     * @param Data $helperData
     * @param CollectionFactory $collectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     * @param FilterProcessor $productCollectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        Data $helperData,
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        FilterProcessor $productCollectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory,
        DateTime $dateTime
    ) {
        $this->helperData                 = $helperData;
        $this->collectionFactory          = $collectionFactory;
        $this->searchCriteriaBuilder      = $searchCriteriaBuilder;
        $this->collectionProcessor        = $collectionProcessor;
        $this->searchResultsFactory       = $searchResultsFactory;
        $this->dateTime                   = $dateTime;
        $this->productCollectionProcessor = $productCollectionProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleProductPage(
        SearchCriteriaInterface $searchCriteria = null,
        SearchCriteriaInterface $productSearchCriteria = null,
        $storeId = null,
        $customerGroup = null
    ) {
        return $this->initParam(
            Type::TYPE_PAGE_PRODUCT,
            $searchCriteria,
            $productSearchCriteria,
            $storeId,
            $customerGroup
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleCategoryPage(
        SearchCriteriaInterface $searchCriteria = null,
        SearchCriteriaInterface $productSearchCriteria = null,
        $storeId = null,
        $customerGroup = null
    ) {
        return $this->initParam(
            Type::TYPE_PAGE_CATEGORY,
            $searchCriteria,
            $productSearchCriteria,
            $storeId,
            $customerGroup
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleCartPage(
        SearchCriteriaInterface $searchCriteria = null,
        SearchCriteriaInterface $productSearchCriteria = null,
        $storeId = null,
        $customerGroup = null
    ) {
        return $this->initParam(
            Type::TYPE_PAGE_SHOPPING,
            $searchCriteria,
            $productSearchCriteria,
            $storeId,
            $customerGroup
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleOSCPage(
        SearchCriteriaInterface $searchCriteria = null,
        SearchCriteriaInterface $productSearchCriteria = null,
        $storeId = null,
        $customerGroup = null
    ) {
        return $this->initParam(Type::TYPE_PAGE_OSC, $searchCriteria, $productSearchCriteria, $storeId, $customerGroup);
    }

    /**
     * @param string $type
     * @param SearchCriteriaInterface|null $searchCriteria
     * @param SearchCriteriaInterface|null $productSearchCriteria
     * @param null $storeId
     * @param null $customerGroup
     *
     * @return SearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function initParam(
        $type,
        SearchCriteriaInterface $searchCriteria = null,
        SearchCriteriaInterface $productSearchCriteria = null,
        $storeId = null,
        $customerGroup = null
    ) {
        if (!$this->helperData->isEnabled($storeId)) {
            throw new NoSuchEntityException(__('The module is disabled'));
        }

        if ($searchCriteria === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }

        if ($productSearchCriteria === null) {
            $productSearchCriteria = $this->searchCriteriaBuilder->create();
        }

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addActiveFilter($customerGroup, $storeId)
            ->addDateFilter($this->dateTime->date())
            ->addTypeFilter($type)
            ->addLocationFilter(['neq' => 'custom']);
        $this->collectionProcessor->process($searchCriteria, $collection);

        foreach ($collection->getItems() as $rule) {
            /** @var Rule $rule */
            $productIds        = $rule->getResource()->getProductListByRuleId($rule->getId());
            $productCollection = $rule->getProductCollection();
            $productCollection->addIdFilter($productIds);
            $this->productCollectionProcessor->process($productSearchCriteria, $productCollection);
            $rule->setMatchingProducts($productCollection->getItems());
        }

        /** @var SearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
