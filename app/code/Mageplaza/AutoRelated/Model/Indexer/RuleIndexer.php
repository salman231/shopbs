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

namespace Mageplaza\AutoRelated\Model\Indexer;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogRule\Model\Indexer\IndexBuilder;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Profiler;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule\Collection as ARPRuleCollection;
use Mageplaza\AutoRelated\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Mageplaza\AutoRelated\Model\Rule;
use Psr\Log\LoggerInterface;

/**
 * Class RuleIndexer
 * @package Mageplaza\AutoRelated\Model\Indexer
 */
class RuleIndexer extends IndexBuilder
{
    /**
     * @var RuleCollectionFactory
     */
    protected $arpRuleCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Product[]
     */
    protected $products;

    /**
     * RuleIndexer constructor.
     *
     * @param CollectionFactory $ruleCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param Config $eavConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateFormat
     * @param DateTime $dateTime
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param RuleCollectionFactory $arpRuleCollectionFactory
     * @param int $batchCount
     */
    public function __construct(
        CollectionFactory $ruleCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Config $eavConfig,
        \Magento\Framework\Stdlib\DateTime $dateFormat,
        DateTime $dateTime,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        RuleCollectionFactory $arpRuleCollectionFactory,
        $batchCount = 1000
    ) {
        $this->connection               = $resource->getConnection();
        $this->productRepository        = $productRepository;
        $this->searchCriteriaBuilder    = $criteriaBuilder;
        $this->arpRuleCollectionFactory = $arpRuleCollectionFactory;
        $this->batchCount               = $batchCount;

        parent::__construct(
            $ruleCollectionFactory,
            $priceCurrency,
            $resource,
            $storeManager,
            $logger,
            $eavConfig,
            $dateFormat,
            $dateTime,
            $productFactory,
            $batchCount
        );
    }

    /**
     * Full reindex
     *
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (Exception $e) {
            $this->critical($e);
            throw new LocalizedException(
                __('Mageplaza AutoRelated indexing failed. See details in exception log.')
            );
        }
    }

    /**
     * Full reindex Rule method
     *
     * @return void
     */
    protected function doReindexFull()
    {
        $this->connection->truncateTable(
            $this->getTable('mageplaza_autorelated_actions_index')
        );

        foreach ($this->getActiveRules() as $rule) {
            $this->execute($rule);
        }
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->doReindexByIds($ids);
        } catch (Exception $e) {
            $this->critical($e);
            throw new LocalizedException(
                __('Mageplaza AutoRelated indexing failed. See details in exception log.')
            );
        }
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @return void
     * @throws Exception
     */
    protected function doReindexByIds($ids)
    {
        $this->cleanByIds($ids);

        $products    = $this->getProducts($ids);
        $activeRules = $this->getActiveRules();
        foreach ($products as $product) {
            $this->applyARPRules($activeRules, $product);
        }
    }

    /**
     * Clean by product ids
     *
     * @param array $productIds
     *
     * @return void
     */
    protected function cleanByIds($productIds)
    {
        $query = $this->connection->deleteFromSelect(
            $this->connection
                ->select()
                ->from($this->resource->getTableName('mageplaza_autorelated_actions_index'), 'product_id')
                ->distinct()
                ->where('product_id IN (?)', $productIds),
            $this->resource->getTableName('mageplaza_autorelated_actions_index')
        );
        $this->connection->query($query);
    }

    /**
     * @param Rule $rule
     * @param Product $product
     *
     * @return $this
     * @throws Exception
     */
    protected function applyARPRule(Rule $rule, $product)
    {
        $this->assignProductToARPRule($rule, $product);

        return $this;
    }

    /**
     * @param ARPRuleCollection $ruleCollection
     * @param Product $product
     *
     * @throws Exception
     */
    protected function applyARPRules(ARPRuleCollection $ruleCollection, Product $product)
    {
        foreach ($ruleCollection as $rule) {
            $this->assignProductToARPRule($rule, $product);
        }
    }

    /**
     * @param Rule $rule
     * @param Product $product
     *
     * @throws Exception
     */
    public function assignProductToARPRule(Rule $rule, Product $product)
    {
        if (!$rule->getActions()->validate($product)) {
            return;
        }

        $ruleId           = (int) $rule->getId();
        $productEntityId  = (int) $product->getId();
        $ruleProductTable = $this->getTable('mageplaza_autorelated_actions_index');
        $this->connection->delete(
            $ruleProductTable,
            [
                'rule_id = ?'    => $ruleId,
                'product_id = ?' => $productEntityId,
            ]
        );

        $rows = [];

        try {
            $rows[] = [
                'rule_id'    => $ruleId,
                'product_id' => $productEntityId,
            ];

            if ($rows) {
                $this->connection->insertMultiple($ruleProductTable, $rows);
            }
        } catch (Exception $e) {
            $this->critical($e);
        }
    }

    /**
     * Reindex data about rule relations with products.
     *
     * @param Rule $rule
     *
     * @return bool
     */
    protected function execute(Rule $rule)
    {
        if (!$rule->getData('is_active')) {
            return false;
        }

        Profiler::start('__MATCH_PRODUCTS__');
        $rule->reindex();
        Profiler::stop('__MATCH_PRODUCTS__');

        return true;
    }

    /**
     * @return ARPRuleCollection
     */
    protected function getAllARPRules()
    {
        return $this->arpRuleCollectionFactory->create();
    }

    /**
     * Get active rules
     *
     * @return ARPRuleCollection
     */
    protected function getActiveRules()
    {
        return $this->getAllARPRules()->addFieldToFilter('is_active', 1);
    }

    /**
     * Get products by ids
     *
     * @param array $productIds
     *
     * @return Product[]
     */
    public function getProducts($productIds)
    {
        if ($this->products === null) {
            $this->searchCriteriaBuilder->addFilter('entity_id', $productIds, 'in');
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $this->products = $this->productRepository->getList($searchCriteria)->getItems();
        }

        return $this->products;
    }
}
