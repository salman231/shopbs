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

namespace Mageplaza\DailyDeal\Helper;

use DateTime;
use Exception;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\DailyDeal\Model\Deal;
use Mageplaza\DailyDeal\Model\DealFactory;
use Mageplaza\DailyDeal\Model\ResourceModel\Deal\Collection;

/**
 * Class Data
 * @package Mageplaza\DailyDeal\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'dailydeal';

    /**
     * @var ProductFactory
     */
    public $_productFactory;

    /**
     * @var StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var DealFactory
     */
    protected $_dealFactory;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param StockRegistryInterface $stockRegistry
     * @param DealFactory $dealFactory
     * @param TimezoneInterface $localeDate
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        StockRegistryInterface $stockRegistry,
        DealFactory $dealFactory,
        TimezoneInterface $localeDate,
        Registry $registry
    ) {
        $this->_productFactory = $productFactory;
        $this->_stockRegistry  = $stockRegistry;
        $this->_dealFactory    = $dealFactory;
        $this->localeDate      = $localeDate;
        $this->_registry       = $registry;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Get Price of product by id
     *
     * @param $productId
     *
     * @return float|int
     */
    public function getProductPrice($productId)
    {
        if ($productId) {
            $product = $this->_productFactory->create();

            return $product->load($productId)->getPrice();
        }

        return 0;
    }

    /**
     * Get Qty of Product by sku
     *
     * @param $sku
     *
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getProductQty($sku)
    {
        if ($sku) {
            return $this->_stockRegistry->getStockItemBySku($sku)->getQty();
        }

        return 0;
    }

    /**
     * Get Qty of Product by product id
     *
     * @param int|null $productId
     *
     * @return float|int
     */
    public function getProductQtyByProductId($productId = null)
    {
        if ($productId) {
            return $this->_stockRegistry->getStockItem($productId)->getQty();
        }

        return 0;
    }

    /**
     * Get Qty of Product by Id
     *
     * @param $productId
     *
     * @return float
     */
    public function getStockItemById($productId)
    {
        return $this->_stockRegistry->getStockItem($productId)->getQty();
    }

    /**
     * @param null $productId
     *
     * @return DataObject
     * @throws NoSuchEntityException
     */
    public function getProductDeal($productId = null)
    {
        $productId = $productId ?: $this->getCurrentProduct()->getId();
        $storeId   = $this->storeManager->getStore()->getId();

        /** @var Collection $dealCollection */
        $dealCollection = $this->_dealFactory->create()->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('product_id', ['eq' => $productId])
            ->addFieldToFilter('store_ids', [
                ['finset' => Store::DEFAULT_STORE_ID],
                ['finset' => $storeId]
            ]);

        return $dealCollection->getSize() ? $dealCollection->setPageSize(1)->getFirstItem() : new DataObject();
    }

    /**
     * Check product deal by product id
     *
     * @param null $productId
     *
     * @return bool
     * @throws Exception
     */
    public function checkDealProduct($productId = null)
    {
        $productId = $productId ?: $this->getCurrentProduct()->getId();

        return $this->checkStatusDeal($productId) === true && $this->checkEndedDeal($productId) === false;
    }

    /**
     * @return array
     */
    public function getDealProductIds()
    {
        /** @var Collection $collection */
        $collection = $this->_dealFactory->create()->getCollection()->addFieldToSelect('product_id');

        return $collection->getColumnValues('product_id');
    }

    /**
     * Get Product Id by Sku
     *
     * @param $sku
     *
     * @return mixed
     */
    public function getProductIdBySku($sku)
    {
        /** @var Collection $dealCollection */
        $dealCollection = $this->_dealFactory->create()->getCollection()
            ->addFieldToSelect('product_sku')
            ->addFieldToFilter('product_sku', ['eq' => $sku]);

        return $dealCollection->setPageSize(1)->getFirstItem()->getProductId();
    }

    /**
     * @param null $storeId
     *
     * @return string
     * @throws Exception
     */
    public function getCurrentDateTime($storeId = null)
    {
        $currentDateTime = (new DateTime())
            ->setTimestamp($this->localeDate->scopeTimeStamp($storeId))
            ->format('Y-m-d H:i:s');

        return $currentDateTime;
    }

    /**
     * @param Deal $deal
     *
     * @return float|int
     */
    public function getRemainTime(Deal $deal)
    {
        $currentDate = $this->getCurrentDateTime();
        $currentTime = strtotime($currentDate);
        $fromDate    = $deal->getDateFrom();
        $toDate      = $deal->getDateTo();
        $remainTime  = 0;
        if (strtotime($toDate) >= $currentTime && strtotime($fromDate) <= $currentTime) {
            $remainTime = (strtotime($toDate) - $currentTime) * 1000;
        }

        return $remainTime;
    }

    /**
     * @param $productId
     *
     * @return bool
     * @throws Exception
     */
    public function checkStatusDeal($productId)
    {
        $dealCollection = $this->getProductDeal($productId);
        $currentDate    = $this->getCurrentDateTime();
        $status         = $dealCollection->getStatus();
        $dateFrom       = $dealCollection->getDateFrom();
        $dateTo         = $dealCollection->getDateTo();
        $dealQty        = $dealCollection->getDealQty();
        $saleQty        = $dealCollection->getSaleQty();

        return (int) $status === 1 &&
            $dealQty > $saleQty &&
            strtotime($dateTo) >= strtotime($currentDate) &&
            strtotime($dateFrom) <= strtotime($currentDate);
    }

    /**
     * @param $productId
     *
     * @return bool
     * @throws Exception
     */
    public function checkEndedDeal($productId)
    {
        $dealCollection = $this->getProductDeal($productId);
        $currentDate    = $this->getCurrentDateTime();
        $status         = $dealCollection->getStatus();
        $dateTo         = $dealCollection->getDateTo();
        $dealQty        = $dealCollection->getDealQty();
        $saleQty        = $dealCollection->getSaleQty();

        return (int) $status === 1 && ($dealQty <= $saleQty || strtotime($dateTo) < strtotime($currentDate));
    }

    /**
     * Check deal disable
     *
     * @param $productId
     *
     * @return bool
     */
    public function checkDisableDeal($productId)
    {
        $dealCollection = $this->getProductDeal($productId);

        return !$dealCollection->getStatus();
    }

    /**
     * Get child product Ids of configuration product by parent id
     *
     * @param null $productId
     *
     * @return array
     * @throws Exception
     */
    public function getChildConfigurableProductIds($productId = null)
    {
        $productId = $productId ?: $this->getCurrentProduct()->getId();

        $childIds = [];
        $product  = $this->_productFactory->create()->load($productId);
        if ($product->getTypeId() === 'configurable') {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($children as $child) {
                $childId = $child->getID();
                if ($this->checkStatusDeal($childId)) {
                    $childIds[] = $childId;
                }
            }
        }

        return $childIds;
    }

    /**
     * Check configuration product
     *
     * @param null $productId
     *
     * @return bool
     * @throws Exception
     */
    public function checkDealConfigurableProduct($productId = null)
    {
        $productId = $productId ?: $this->getCurrentProduct()->getId();

        if ($this->getStockItemById($productId) > 0) {
            return (bool) $this->getChildConfigurableProductIds($productId);
        }

        return false;
    }

    /**
     * Get parent product id by child product id
     *
     * @param $productId
     *
     * @return mixed
     */
    public function getParentIdByChildId($productId)
    {
        if ($this->getStockItemById($productId) > 0) {
            $objectManager = ObjectManager::getInstance();
            $productConfig = $objectManager
                ->create(Configurable::class)
                ->getParentIdsByChild($productId);
            if (isset($productConfig[0])) {
                return $productConfig[0];
            }

            $productGrouped = $objectManager
                ->create(Grouped::class)
                ->getParentIdsByChild($productId);
            if (isset($productGrouped[0])) {
                return $productGrouped[0];
            }

            return $productId;
        }

        return 0;
    }

    /**
     * Get parent product ids by child product ids
     *
     * @param array $productIds
     *
     * @return array
     */
    public function getProductIdsParent(array $productIds)
    {
        $ids = [];

        foreach ($productIds as $productId) {
            $ids[] = $this->getParentIdByChildId($productId);
        }

        return $ids;
    }

    /**
     * get Deal Price
     *
     * @param $id
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getDealPrice($id)
    {
        $deal            = $this->getProductDeal($id);
        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency();
        $price           = $this->storeManager->getStore()
            ->getBaseCurrency()
            ->convert($deal->getDealPrice(), $currentCurrency);

        return $price;
    }

    /**
     * get Default Deal Price
     *
     * @param $id
     *
     * @return mixed
     */
    public function getDefaultDealPrice($id)
    {
        $deal = $this->getProductDeal($id);

        return $deal->getDealPrice();
    }

    /**
     * Get format price
     *
     * @param $price
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function formatPrice($price)
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->format($price);
    }

    /**
     * get Current Product
     *
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * get current category
     *
     * @return mixed
     */
    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * Retrieve category rewrite suffix for store
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getUrlSuffix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function getCategory($store = null)
    {
        $categoryIds = $this->getConfigValue('dailydeal/create_deal/category', $store);
        if (!is_array($categoryIds)) {
            $categoryIds = explode(',', $categoryIds);
        }

        return $categoryIds;
    }
}
