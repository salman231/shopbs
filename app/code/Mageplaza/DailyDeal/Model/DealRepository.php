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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Model;

use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\DailyDeal\Api\Data\DailyDealInterface;
use Mageplaza\DailyDeal\Api\DealRepositoryInterface;
use Mageplaza\DailyDeal\Helper\Data;
use Mageplaza\DailyDeal\Model\ResourceModel\Deal as ResourceModel;
use Mageplaza\DailyDeal\Model\ResourceModel\Deal\Collection;
use Mageplaza\DailyDeal\Model\ResourceModel\Deal\CollectionFactory;

/**
 * Class DealRepository
 * @package Mageplaza\DailyDeal\Model
 */
class DealRepository implements DealRepositoryInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var DealFactory
     */
    protected $dealFactory;

    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * @var CollectionFactory
     */
    protected $dealCollectionFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * DealRepository constructor.
     *
     * @param Data $helperData
     * @param ProductRepository $productRepository
     * @param DealFactory $dealFactory
     * @param ResourceModel $resourceModel
     * @param CollectionFactory $dealCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Data $helperData,
        ProductRepository $productRepository,
        DealFactory $dealFactory,
        ResourceModel $resourceModel,
        CollectionFactory $dealCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->helperData            = $helperData;
        $this->productRepository     = $productRepository;
        $this->dealFactory           = $dealFactory;
        $this->resourceModel         = $resourceModel;
        $this->dealCollectionFactory = $dealCollectionFactory;
        $this->collectionProcessor   = $collectionProcessor;
        $this->searchResultsFactory  = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getAllDeals(SearchCriteriaInterface $searchCriteria = null)
    {
        if (!$this->helperData->isEnabled()) {
            throw new NoSuchEntityException(__('The module is disabled'));
        }

        if ($searchCriteria === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }
        /** @var Collection $collection */
        $collection = $this->dealCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        /** @var Deal $deal */
        foreach ($collection->getItems() as $deal) {
            $deal->setRemainingTime($this->helperData->getRemainTime($deal));
        }
        /** @var SearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($ruleId)
    {
        if (!$this->helperData->isEnabled()) {
            throw new NoSuchEntityException(__('The module is disabled'));
        }
        /** @var Deal $model */
        $model = $this->dealFactory->create();
        $this->resourceModel->load($model, $ruleId);

        if ($model->getId() !== $ruleId) {
            throw new NoSuchEntityException(__('Deal with ID %1 doesn\'t exist', $ruleId));
        }
        try {
            $this->resourceModel->delete($model);

            return true;
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('The deal data is invalid. Verify the data and try again.'));
        }
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        if (!$this->helperData->isEnabled()) {
            return null;
        }
        /** @var Deal $model */
        $model = $this->dealFactory->create();
        $this->resourceModel->load($model, $id);

        if ($model->getId() !== $id) {
            throw new NoSuchEntityException(__('Deal with ID %1 doesn\'t exist', $id));
        }

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function getByProductSku($sku)
    {
        $productId = $this->productRepository->get($sku)->getId();

        if (!$this->helperData->checkStatusDeal($productId)) {
            return null;
        }
        /** @var Deal $dealData */
        $dealData = $this->helperData->getProductDeal($productId);

        if (!$dealData->getId()) {
            return null;
        }

        $dealData->setRemainingTime($this->helperData->getRemainTime($dealData));

        return $dealData;
    }

    /**
     * @inheritDoc
     */
    public function add($deal)
    {
        if (!$this->helperData->isEnabled()) {
            throw new NoSuchEntityException(__('The module is disabled'));
        }

        /** @var Deal $model */
        $model = $this->dealFactory->create();
        if ($deal->getId()) {
            $this->resourceModel->load($model, $deal->getId());
            if ((int) $model->getId() !== (int) $deal->getDealId()) {
                throw new NoSuchEntityException(__('The deal doesn\'t exist.'));
            }
        }
        $this->checkRequireField($deal);
        $sku     = $deal->getProductSku();
        $dealQty = $deal->getDealQty();
        if (!$deal->getStoreIds()) {
            $deal->setStoreIds(0);
        }
        /** @var Collection $dealCollection */
        $dealCollection = $this->dealFactory->create()->getCollection()
            ->addFieldToSelect('product_sku')
            ->addFieldToFilter('product_sku', ['eq' => $sku]);
        if (!$dealCollection->getSize() || $model->getId()) {
            if ($this->helperData->getProductQty($sku) >= $dealQty) {
                $model->addData(Data::jsonDecode(Data::jsonEncode($deal)));
                try {
                    $this->resourceModel->save($model);

                    return true;
                } catch (Exception $e) {
                    throw new CouldNotSaveException(__('The deal data can\'t be saved. Verify the data and try again.'));
                }
            } else {
                throw new NoSuchEntityException(__('Deal qty must be less than or equal to product qty'));
            }
        } else {
            throw new NoSuchEntityException(__('Already set Deal for this product.'));
        }
    }

    /**
     * @param Deal $deal
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function checkRequireField($deal)
    {
        $requiredFields = [
            DailyDealInterface::PRODUCT_SKU,
            DailyDealInterface::STATUS,
            DailyDealInterface::DEAL_PRICE,
            DailyDealInterface::DEAL_QTY,
            DailyDealInterface::DATE_FROM,
            DailyDealInterface::DATE_TO,
        ];
        foreach ($requiredFields as $requiredField) {
            $checkField = $deal->getData($requiredField);
            if ($checkField === '' || $checkField === null) {
                throw new NoSuchEntityException(__('Missing %1 field.', $requiredField));
            }
        }
        if (strtotime($deal->getDateFrom()) > strtotime($deal->getDateTo())) {
            throw new NoSuchEntityException(__('End on must be greater than start on'));
        }
        $product = $this->productRepository->get($deal->getProductSku());
        if (!$product) {
            throw new NoSuchEntityException(__('The product with SKU %1 doesn\'t exist.', $deal->getProductSku()));
        }
        if (($deal->getProductId() && (int) $product->getId() !== (int) $deal->getProductId())
            || ($deal->getProductName() && (int) $product->getName() !== (int) $deal->getProductName())
        ) {
            throw new NoSuchEntityException(__('The product data is invalid. Verify the data and try again.'));
        }
        if ($deal->getDealPrice() > $product->getPrice()) {
            throw new NoSuchEntityException(
                __(
                    'Deal price must be less than or equal to original price %1 and greater than 0',
                    $product->getPrice()
                )
            );
        }
        $deal->setProductId($product->getId());
        $deal->setProductName($product->getName());

        if ((int) $deal->getStatus() !== 0 && (int) $deal->getStatus() !== 1) {
            throw new NoSuchEntityException(__('The status must have a value of 0 or 1'));
        }

        return $deal;
    }
}
