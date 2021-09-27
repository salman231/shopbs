<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpreportsystem\Helper;

use Webkul\Mpreportsystem\Model\Product as SellerProduct;
use Magento\Sales\Model\ResourceModel\Order;
use Webkul\Marketplace\Model\SaleslistFactory;
use Magento\Framework\Locale\ListsInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Webkul\Marketplace\Model\ResourceModel;

/**
 * Webkul Mpreportsystem Helper Data.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_marketplaceProductCollection;

    /**
     * @var Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $_salesStatusCollection;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Webkul\Marketplace\Model\SaleslistFactory
     */
    protected $_marketplaceSalesList;

    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory
     */
    protected $_mpSalesListCollection;

    /**
     * @var Magento\Framework\Locale\ListsInterface
     */
    protected $_listInterface;

    /**
     * @var Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var string
     */
    protected $_deploymentConfigDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_storeTime;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param ResourceModel\Product\CollectionFactory $marketplaceProductCollection
     * @param Order\Status\CollectionFactory $salesStatusCollection
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param SaleslistFactory $salesListFactory
     * @param ResourceModel\Saleslist\CollectionFactory $mpSalesListFactory
     * @param ListsInterface $listInterface
     * @param RegionFactory $regionFactory
     * @param DeploymentConfig $deploymentConfig
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyObject
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        ResourceModel\Product\CollectionFactory $marketplaceProductCollection,
        Order\Status\CollectionFactory $salesStatusCollection,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        SaleslistFactory $salesListFactory,
        ResourceModel\Saleslist\CollectionFactory $mpSalesListFactory,
        ListsInterface $listInterface,
        RegionFactory $regionFactory,
        DeploymentConfig $deploymentConfig,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyObject
    ) {
        $this->_priceCurrency = $priceCurrencyObject;
        $this->_productFactory = $productFactory;
        parent::__construct($context);
        $this->_localeCurrency = $localeCurrency;
        $this->_storeManager = $storeManager;
        $this->_marketplaceProductCollection = $marketplaceProductCollection;
        $this->_salesStatusCollection = $salesStatusCollection;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_orderFactory = $orderFactory;
        $this->_marketplaceSalesList = $salesListFactory;
        $this->_mpSalesListCollection = $mpSalesListFactory;
        $this->_listInterface = $listInterface;
        $this->_regionFactory = $regionFactory;
        $this->_deploymentConfigDate = $deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE
        );
        $this->_mpHelper = $mpHelper;
        $this->_storeTime = $timezone;
    }

    /**
     * Get cutsomer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }

    /**
     * Get all product ids of seller
     *
     * @return array
     */
    public function getSellerProductIds()
    {
        $sellerId = $this->getCustomerId();
        $productIds = [];
        if ($sellerId) {
            $productCollection = $this->_marketplaceProductCollection->create()
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
            foreach ($productCollection as $product) {
                $productIds[] = $product->getMageproductId();
            }
        }
        return $productIds;
    }

    /**
     * Get all category ids
     *
     * @return array
     */
    public function getAllCategoriesId()
    {
        $categoryIds = [];
        $categoryCollection = $this->_categoryFactory->create()
            ->getCollection();
        foreach ($categoryCollection as $category) {
            $categoryIds[] = $category->getId();
        }
        return $categoryIds;
    }

    /**
     * Get all category ids of an product by product id
     *
     * @param int $productId
     *
     * @return array
     */
    public function getCategoryIdsByProductId($productId)
    {
        $categoryIds = [];
        $productModel = $this->_productFactory->create()->load($productId);
        if ($productModel->getCategoryIds() &&
            !empty($productModel->getCategoryIds())
        ) {
            $categoryIds = array_unique($productModel->getCategoryIds());
        }

        return $categoryIds;
    }

    /**
     * Get category ids by product ids
     *
     * @param array $productIds
     *
     * @return array
     */
    public function getCategoryIdsByProductIds($productIds)
    {
        $categoryIdarray = [];
        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $categoryIds = $this->getCategoryIdsByProductId($productId);
                foreach ($categoryIds as $categoryKey => $categoryId) {
                    if (!in_array($categoryId, $categoryIdarray)) {
                        $categoryIdarray[] = $categoryId;
                    }
                }
            }
        }

        return $categoryIdarray;
    }

    /**
     * Get all order status
     *
     * @return void
     */
    public function getOrderStatus()
    {
        $orderStatusUpdatedArray = [];
        $orderStatusArray = $this->_salesStatusCollection
            ->create()->toOptionArray();
        foreach ($orderStatusArray as $key => $orderStatus) {
            if ($orderStatus['value']!='closed') {
                $orderStatusUpdatedArray[$orderStatus['value']] =
                $orderStatus['label'];
            }
        }
        return $orderStatusUpdatedArray;
    }

    /**
     * get all productids of categories which are in an array
     *
     * @param array $categoryIds
     * @return array
     */
    public function getProductIdsByCategoryIds($categoryIds = [])
    {
        $productIds = [];
        if (!empty($categoryIds)) {
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addCategoriesFilter(['in' => $categoryIds]);
            foreach ($collection as $productModel) {
                $productIds[] = $productModel->getEntityId();
            }
        }
        return $productIds;
    }

    /**
     * get category collection either for admin or for seller
     *
     * @param int $flag
     * @return array
     */
    public function getCategoriesCollection($flag)
    {
        $categoryArray = [];
        if ($flag==0) {
            $sellerProductIds = $this->getSellerProductIds();
            $categories = $this->getCategoryIdsByProductIds($sellerProductIds);
        } else {
            $categories = $this->getAllCategoriesId();
        }
        $categoryCollection = $this->_categoryFactory->create()->getCollection()
            ->addFieldToFilter('entity_id', ['in'=>$categories]);
        foreach ($categoryCollection as $category) {
            $categoryData = $this->getCategory($category->getEntityId());
            $categoryArray[$category->getEntityId()] = $categoryData->getName();
        }
        return $categoryArray;
    }

    /**
     * load an category by category id
     *
     * @param int $categoryId
     * @return array
     */
    public function getCategory($categoryId)
    {
        $category = $this->_categoryFactory->create();
        $category->load($categoryId);
        return $category;
    }

    /**
     * get product collection data for top selling product graph
     *
     * @param array $data
     * @return array
     */
    public function getProductsSalesCollection($data)
    {
        $quoteitemTable = $this->_marketplaceProductCollection
            ->create()
            ->getTable('quote_item');
        $productCount = $this->gettopPtoductCountSetting();
        $productQuantityCollection = [];
        if (array_key_exists('seller_id', $data)) {
            $sellerId = $data['seller_id'];
        }
        $sellerId = '';
        if (array_key_exists('seller_id', $data)) {
            $sellerId = $data['seller_id'];
        }
        $collection = $this->getSalesListCollection($sellerId, $data);
        $collection = $this->getProductCollectionByFilter($data, $collection);
        $collection->getSelect()
            ->columns('SUM(magequantity) AS qty')
            ->group('mageproduct_id');

        $collection->setOrder('qty', 'DESC');
        $collection->setPageSize($productCount)
            ->setCurPage(1);
        if ($collection->getSize()) {
            foreach ($collection as $salesItem) {
                $productId = $salesItem->getMageproductId();
                if (array_key_exists($productId, $productQuantityCollection)) {
                    $productQuantityCollection[$productId]['qty'] =
                    $productQuantityCollection[$productId]['qty'] + $salesItem->getQty();
                } else {
                    $productQuantityCollection[$productId]['name'] = $salesItem->getmageproName();
                    $productQuantityCollection[$productId]['qty'] = $salesItem->getQty();
                }
            }
        }
        return $this->getFormattedData1($productQuantityCollection);
    }

    /**
     * format product collection data for graph
     *
     * @param array $data
     * @return array
     */
    public function getFormattedData1($data)
    {
        $returnData = [];
        $returnKey = [];
        $result = [];
        $returnName = [];
        $totalQty = 0;
        if (is_array($data)) {
            foreach ($data as $datakey => $datavalue) {
                $returnData[] = $datavalue['qty'];
                $returnKey[] = $datakey;
                $returnName[] = $datavalue['name'];
                $totalQty = $totalQty + $datavalue['qty'];
            }
        }
        $result['returnData'] = $returnData;
        $result['returnKey'] = $returnKey;
        $result['totalQty'] = $totalQty;
        $result['name'] = $returnName;
        return $result;
    }

    /**
     * get saleslist collecion by filter
     *
     * @param array $data
     * @return collection
     */
    public function getProductCollectionByFilter($data, $orderCollection = null)
    {
        $sellerId = '';
        if (array_key_exists('seller_id', $data)) {
            $sellerId = $data['seller_id'];
        }
        $sellerIdFlag = 0;
        if ($sellerId!='') {
            $sellerIdFlag = 1;
        }
        if (empty($orderCollection)) {
            $orderCollection = $this->_mpSalesListCollection
            ->create();
        }
        if ($orderCollection->getSize()) {
            $orderCollection->addFieldToFilter(
                'main_table.parent_item_id',
                ['null' =>  true]
            );

            if ($sellerIdFlag) {
                $orderCollection->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
            }
            if ($data['filter']=='month') {
                $orderCollection->addFieldToFilter(
                    'created_at',
                    [
                        'datetime' => true,
                        'from' => date('Y-m').'-01 00:00:00',
                        'to' => date('Y-m').'-31 23:59:59',
                    ]
                );
            } elseif ($data['filter']=='week') {
                $firstDayOfWeek = date('Y-m-d', strtotime('Last Monday', time()));
                $lastDayOfWeek = date('Y-m-d', strtotime('Next Sunday', time()));
                $orderCollection->addFieldToFilter(
                    'created_at',
                    [
                        'datetime' => true,
                        'from' => $firstDayOfWeek.' 00:00:00',
                        'to' => $lastDayOfWeek.' 23:59:59',
                    ]
                );
            } elseif ($data['filter']=='day') {
                $orderCollection->addFieldToFilter(
                    'created_at',
                    [
                        'datetime' => true,
                        'from' => date('Y-m-d').' 00:00:00',
                        'to' => date('Y-m-d').' 23:59:59',
                    ]
                );
            } else {
                $curryear = date('Y');
                $date1 = $curryear.'-01-01 00:00:00';
                $date2 = $curryear.'-12-31 23:59:59';
                $orderCollection->addFieldToFilter(
                    'created_at',
                    [
                        'datetime' => true,
                        'from' => $date1,
                        'to' => $date2,
                    ]
                );
            }
        }
        return $orderCollection;
    }

    /**
     * get country sales collection data for geo location graph
     *
     * @param array $data
     * @return array
     */
    public function getCountrySalesCollection($data)
    {
        $productQuantityCollection = [];
        $sellerId = '';
        if (array_key_exists('seller_id', $data)) {
            $sellerId = $data['seller_id'];
        }
        $sellerOrderCollection = $this->getSalesListCollection($sellerId, $data);
        $sellerOrderCollection = $this->getProductCollectionByFilter($data, $sellerOrderCollection);
        $orderSaleArray = [];
        $orderIds = [];
        foreach ($sellerOrderCollection as $record) {
            $orderId = $record->getOrderId();
            $orderIds[] = $record->getOrderId();
            if (!isset($orderSaleArray[$record->getOrderId()])) {
                $orderSaleArray[$record->getOrderId()] =
                $record->getActualSellerAmount() + $record->getTotalTax();
            } else {
                $orderSaleArray[$orderId] =
                $orderSaleArray[$orderId] + $record->getActualSellerAmount() + $record->getTotalTax();
            }
        }
        $updatedOrderIds = array_unique($orderIds);
        $orderCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'entity_id',
                ['in' => $orderIds]
            );
        $returnData = $this->getArrayData($orderCollection, $orderSaleArray);
        return $returnData;
    }

    /**
     * get formatted data for geo location graph
     *
     * @param collection $collection
     * @param array $orderSaleArray
     * @return array
     */
    public function getArrayData($collection, $orderSaleArray)
    {
        $countryArray = [];
        $countryRegionArray = [];
        $countrySaleArray = [];
        $countryOrderCountArray = [];
        foreach ($collection as $record) {
            if ($record->getIsVirtual()) {
                $addressData = $record->getBillingAddress()->getData();
            } else {
                $addressData = $record->getShippingAddress()->getData();
            }
            $countryId = $addressData['country_id'];
            $countryName =$this->_listInterface
                ->getCountryTranslation($countryId);
            $countryArray[$countryId] = $countryName;
            if (isset($orderSaleArray[$record->getId()])) {
                if (!isset($countryRegionArray[$countryId])) {
                    $countryRegionArray[$countryId] = [];
                }
                if (!isset($countrySaleArray[$countryId])) {
                    $countrySaleArray[$countryId] = [];
                }
                if (!isset($countryOrderCountArray[$countryId])) {
                    $countryOrderCountArray[$countryId] = [];
                }
                if ($addressData['region_id']) {
                    $regionId = $addressData['region_id'];
                    $region = $this->getRegionById($regionId);
                    $regionCode = $region->getCode();
                    $countryRegionArray[$countryId][$regionCode] =
                        strtoupper($countryId).
                        '-'.
                        strtoupper($regionCode);

                    if (!isset($countrySaleArray[$countryId][$regionCode])) {
                        $countrySaleArray[$countryId][$regionCode] =
                        $orderSaleArray[$record->getId()];
                        $countryOrderCountArray[$countryId][$regionCode] = 1;
                    } else {
                        $countrySaleArray[$countryId][$regionCode] =
                            $countrySaleArray[$countryId][$regionCode] +
                            $orderSaleArray[$record->getId()];
                        $countryOrderCountArray[$countryId][$regionCode] =
                            $countryOrderCountArray[$countryId][$regionCode] +
                            1;
                    }
                } else {
                    $countryRegionArray[$countryId][$countryId] =
                    strtoupper($countryId);
                    if (!isset($countrySaleArray[$countryId][$countryId])) {
                        $countrySaleArray[$countryId][$countryId] =
                        $orderSaleArray[$record->getId()];
                        $countryOrderCountArray[$countryId][$countryId] = 1;
                    } else {
                        $countrySaleArray[$countryId][$countryId] =
                            $countrySaleArray[$countryId][$countryId] +
                            $orderSaleArray[$record->getId()];
                        $countryOrderCountArray[$countryId][$countryId] =
                            $countryOrderCountArray[$countryId][$countryId] +
                            1;
                    }
                }
            }
        }
        $data['country_arr'] = $countryArray;
        $data['country_sale_arr'] = $countrySaleArray;
        $data['country_order_count_arr'] = $countryOrderCountArray;
        $data['country_region_arr'] = $countryRegionArray;
        return $data;
    }

    /**
     * get best customer collection
     *
     * @param array $data
     * @return array
     */
    public function getCustomerCollection($data)
    {
        $productQuantityCollection = [];
        $sellerId = '';
        if (array_key_exists('seller_id', $data)) {
            $sellerId = $data['seller_id'];
        }
        $sellerIdFlag = 0;
        if ($sellerId!='') {
            $sellerIdFlag = 1;
        }
        $sellerOrderCollection = $this->getProductCollectionByFilter($data);
        $orderSaleArr = [];
        $orderIds = [];
        $returnData = [];
        $customerCount = $this->getCustomerCountSetting();
        $lastPurchase = [];

        $marketplaceSalesListTable = $this->_marketplaceProductCollection
            ->create()
            ->getTable('marketplace_saleslist');

        $customerEntityTable = $this->_marketplaceProductCollection
            ->create()
            ->getTable('customer_grid_flat');

        $salesCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToSelect(
                [
                    'customer_id',
                    'entity_id',
                    'created_at'
                ]
            )
            ->setOrder('created_at', 'DESC');
        foreach ($salesCollection as $salesOrder) {
            if (!array_key_exists(
                $salesOrder->getCustomerId(),
                $lastPurchase
            )) {
                $lastPurchase[$salesOrder->getCustomerId()] =
                $salesOrder->getCreatedAt();
            }
        }

        $orderCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToSelect(['customer_id','entity_id']);

        $orderCollection->getSelect()
            ->join(
                $customerEntityTable.' as customer',
                'main_table.customer_id = customer.entity_id',
                [
                    'customer_name'=>'name',
                    'registration_date'=>'created_at'
                ]
            );
        if ($sellerIdFlag) {
            $orderCollection->getSelect()
                ->join(
                    $marketplaceSalesListTable.' as mpsaleslist',
                    ' main_table.entity_id = mpsaleslist.order_id',
                    [
                        'mpsaleslist.actual_seller_amount',
                        'mpsaleslist.created_at'
                    ]
                )
                ->where(
                    'mpsaleslist.seller_id = '.$sellerId
                );
            $orderCollection->getSelect()
            ->columns('SUM(actual_seller_amount + total_tax) AS seller_amount')
            ->columns('count(DISTINCT order_id) AS total_order')
            ->group('main_table.customer_id');
        } else {
            $orderCollection->getSelect()
                ->join(
                    $marketplaceSalesListTable.' as mpsaleslist',
                    ' main_table.entity_id = mpsaleslist.order_id',
                    [
                        'mpsaleslist.actual_seller_amount',
                        'mpsaleslist.total_commission',
                        'mpsaleslist.created_at'
                    ]
                );
            $orderCollection->getSelect()
            ->columns('SUM(actual_seller_amount + total_commission + total_tax) AS seller_amount')
            ->columns('count(DISTINCT order_id) AS total_order')
            ->group('main_table.customer_id');
        }

        $productIds = [];
        $categoryFlag = 0;
        $orderIds = [];
        $orderFlag = 0;
        if (array_key_exists('categories', $data) &&
            is_array($data['categories'])
        ) {
            $productIds = $this->getProductIdsByCategoryIds(
                $data['categories']
            );
            $categoryFlag = 1;
        }
        if (array_key_exists(
            'orderstatus',
            $data
        ) && is_array(
            $data['orderstatus']
        )) {
            $orderIds = $this->getOrderIdsByOrderStatus($data['orderstatus']);
            $orderFlag = 1;
        }
        if ($categoryFlag) {
            $orderCollection->addFieldToFilter(
                'mageproduct_id',
                ['in' => $productIds]
            );
        }
        if ($orderFlag) {
            $orderCollection->addFieldToFilter(
                'order_id',
                ['in' => $orderIds]
            );
        }
        $orderCollection->setOrder('seller_amount', 'DESC');
        $orderCollection->setPageSize($customerCount)
            ->setCurPage(1);
            $returnData = $this->getCustomerArrayData(
                $orderCollection,
                $lastPurchase
            );
        return $returnData;
    }

    /**
     * convert customer collection data to an array
     *
     * @param Collection $orderCollection
     * @param array $lastPurchase
     * @return array
     */
    public function getCustomerArrayData($orderCollection, $lastPurchase)
    {
        $customerIds = [];
        $customerOrderCount = [];
        $customerTotalSale = [];
        $customerIdArray = [];
        $data = [];
        foreach ($orderCollection as $order) {
            $orderData = $order->getData();
            $tempArray = [];
            $tempArray['refused'] = $this->getRefusedOrder(['canceled','closed'], $orderData['customer_id']);
            $tempArray['totalSale'] = $orderData['seller_amount'];
            $tempArray['totalcount'] = $orderData['total_order'];
            $lastdate = $this->_storeTime->date($lastPurchase[$orderData['customer_id']], null, false);
            $tempArray['lastpurchase'] = $lastdate->format('Y-m-d H:i:s');
            $tempArray['customer_name'] = $orderData['customer_name'];
            $date = $this->_storeTime->date($orderData['registration_date'], null, false);
            $tempArray['registration_date'] = $date->format('Y-m-d H:i:s');
            $data[$order->getCustomerId()] = $tempArray;
        }
        return $data;
    }

    /**
     * Retrieve currency Symbol.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_localeCurrency->getCurrency(
            $this->getBaseCurrencyCode()
        )->getSymbol();
    }

    /**
     * get base currency symbol
     *
     * @return void
     */
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * get sales collection data for sales graph
     *
     * @param array $data
     * @return array
     */
    public function getSalesAmount($data)
    {
        $sellerId = '';
        if (array_key_exists('seller_id', $data)) {
            $sellerId = $data['seller_id'];
        }
        $totalSales = 0;
        if (array_key_exists('filter', $data)) {
            if ($data['filter']=='year') {
                $returnData = $this->getYearlySale($sellerId, $data);
            } elseif ($data['filter']=='month') {
                $returnData = $this->getMonthSale($sellerId, $data);
            } elseif ($data['filter']=='week') {
                $returnData = $this->getWeeklySale($sellerId, $data);
            } elseif ($data['filter']=='day') {
                $returnData = $this->getDailySale($sellerId, $data);
            }
        } else {
            $returnData = $this->getYearlySale($sellerId, $data);
        }

        return $returnData;
    }

    /**
     * get year sales data according to year fiter
     *
     * @param int $sellerId
     * @param array $paramData
     * @return array
     */
    public function getYearlySale($sellerId, $paramData)
    {
        $totalSaleAmount = 0;
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $monthsArr = [
            '',
            __('January'),
            __('February'),
            __('March'),
            __('April'),
            __('May'),
            __('June'),
            __('July'),
            __('August'),
            __('September'),
            __('October'),
            __('November'),
            __('December'),
        ];
        for ($i = 1; $i <= $currMonth; ++$i) {
            $date1 = $curryear.'-'.$i.'-01 00:00:00';
            $date2 = $curryear.'-'.$i.'-31 23:59:59';
            $collection = $this->getSalesListCollection($sellerId, $paramData);
            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $date1, 'to' => $date2]
            );
            
            $totalSale = 0;
            foreach ($collection as $record) {
                $totalSale = $totalSale + $record->getActualSellerAmount() + $record->getTotalTax();
            }
            $totalSaleAmount = $totalSaleAmount + $totalSale;
            $data['values'][$i] = $this->getCurrentAmount($totalSale);
            if ($i != $currMonth) {
                $data['chxl'] = $data['chxl'].$monthsArr[$i].'|';
            } else {
                $data['chxl'] = $data['chxl'].$monthsArr[$i];
            }
            $data['totalsale'] = $totalSaleAmount;
        }
        return $data;
    }

    /**
     * get month sales data according to month filter
     *
     * @param int $sellerId
     * @param array $paramData
     * @return array
     */
    public function getMonthSale($sellerId, $paramData)
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $currDays = date('d');
        $countChxlLabel = 0;
        for ($i = 1; $i <= $currDays; ++$i) {
            $date1 = $curryear.'-'.$currMonth.'-'.$i.' 00:00:00';
            $date2 = $curryear.'-'.$currMonth.'-'.$i.' 23:59:59';
            $salesCollection = $this->getSalesListCollection(
                $sellerId,
                $paramData
            );
            $salesCollection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $date1, 'to' => $date2]
            );
            $sum = [];
            $totalSales = 0;
            foreach ($salesCollection as $record) {
                $totalSales = $totalSales + $record->getActualSellerAmount() + $record->getTotalTax();
            }
            $price = $totalSales;
            if ($i != $currDays) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].$i.'|';
                $countChxlLabel++;
            }
            if ($i == $currDays) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].$i;
                $countChxlLabel++;
            }
            
        }
        $data['chxlParamCount'] = $countChxlLabel;
        return $data;
    }

    /**
     * get weekly sales data according to weekly sales
     *
     * @param int $sellerId
     * @param array $paramData
     * @return array
     */
    public function getWeeklySale($sellerId, $paramData)
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $currDays = date('d');
        $currWeekDay = date('N');
        $currWeekStartDay = $currDays - $currWeekDay;
        $currWeekEndDay = $currWeekStartDay + 7;
        $currentDayOfMonth=date('j');
        if ($currWeekEndDay > $currentDayOfMonth) {
            $currWeekEndDay = $currentDayOfMonth;
        }
        for ($i = $currWeekStartDay + 1; $i <= $currWeekEndDay; ++$i) {
            $date1 = $curryear.'-'.$currMonth.'-'.$i.' 00:00:00';
            $date2 = $curryear.'-'.$currMonth.'-'.$i.' 23:59:59';
            $collection = $this->getSalesListCollection(
                $sellerId,
                $paramData
            );
            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $date1, 'to' => $date2]
            );
            $sum = [];
            $temp = 0;
            foreach ($collection as $record) {
                $temp = $temp + $record->getActualSellerAmount() + $record->getTotalTax();
            }
            $price = $temp;
            if ($i != $currWeekEndDay) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].
                $currMonth.'/'.$i.
                '/'.$curryear.'|';
            }
            if ($i == $currWeekEndDay) {
                $data['values'][$i] = $this->getCurrentAmount($price);
                $data['chxl'] = $data['chxl'].
                $currMonth.'/'.
                $i.'/'.$curryear;
            }
        }
        return $data;
    }

    /**
     * get currenct day sales collection
     *
     * @param int $sellerId
     * @param array $paramData
     * @return array
     */
    public function getDailySale($sellerId, $paramData)
    {
        $data = [];
        $data['values'] = [];
        $data['chxl'] = '0:|';
        $curryear = date('Y');
        $currMonth = date('m');
        $currDays = date('d');
        $currTime = date('G');
        $arr = [];
        $countValues = 0;
        for ($i = 0; $i <= 23; $i++) {
            for ($k=1; $k <= 2; $k++) {
                $xAxisLabel = '';
                if ($k == 1) {
                    $date1 = $curryear.'-'.$currMonth.'-'.$currDays.' '.$i.':00:00';
                    $updatedDate1 = $this->_storeTime->convertConfigTimeToUtc($date1);
                    $date2 = $curryear.'-'.$currMonth.'-'.$currDays.' '.$i.':30:00';
                    $updatedDate2 = $this->_storeTime->convertConfigTimeToUtc($date2);
                    $xAxisLabel = $i.':00';
                } elseif ($k == 2) {
                    $date1 = $curryear.'-'.$currMonth.'-'.$currDays.' '.$i.':30:00';
                    $updatedDate1 = $this->_storeTime->convertConfigTimeToUtc($date1);
                    $j = $i+1;
                    $date2 = $curryear.'-'.$currMonth.'-'.$currDays.' '.$j.':00:00';
                    $updatedDate2 = $this->_storeTime->convertConfigTimeToUtc($date2);
                    $xAxisLabel = ' ';
                }
                $collection = $this->getSalesListCollection(
                    $sellerId,
                    $paramData
                );
                $collection->addFieldToFilter(
                    'order_id',
                    ['neq' => 0]
                );
                $collection->addFieldToFilter(
                    'created_at',
                    [
                        'datetime' => true,
                        'from' => $updatedDate1,
                        'to' => $updatedDate2
                    ]
                );
                $sum = [];
                $totalSales = 0;
                foreach ($collection as $record) {
                    $totalSales = $totalSales + $record->getActualSellerAmount() + $record->getTotalTax();
                }
                $price = $totalSales;
                if ($i != 23) {
                    $data['values'][$countValues] = $this->getCurrentAmount($price);
                    $data['chxl'] = $data['chxl'].$xAxisLabel.'|';
                }
                if ($i == 23) {
                    if ($k == 2) {
                        $data['values'][$countValues] = $this->getCurrentAmount($price);
                        $data['chxl'] = $data['chxl'].' |';
                        $data['values'][$countValues] = 0;
                        $data['chxl'] = $data['chxl'].'24:00';
                    } elseif ($k == 1) {
                        $data['values'][$countValues] = $this->getCurrentAmount($price);
                        $data['chxl'] = $data['chxl'].$xAxisLabel.'|';
                    }
                }
                
                $countValues++;
            }
        }
        return $data;
    }

    /**
     * get all the order ids of selected order status
     *
     * @param array $orderStatus
     * @return array
     */
    public function getOrderIdsByOrderStatus($orderStatus)
    {
        $orderCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', ['in'  =>  $orderStatus]);
        $orderIds = $this->getFieldArrayFromCollection(
            $orderCollection,
            'entity_id'
        );
        return $orderIds;
    }

    /**
     * get count of all the refused order of selected
     * order status and customer
     *
     * @param array $status
     * @param int $customerId
     * @return int
     */
    public function getRefusedOrder($status, $customerId)
    {
        $orderCollection = $this->_orderFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', ['in'  =>  $status])
            ->addFieldToFilter('customer_id', $customerId);
        return count($orderCollection);
    }

    /**
     * get collection data for particular field
     *
     * @param Collection $collection
     * @param string $field
     * @return array
     */
    public function getFieldArrayFromCollection($collection, $field)
    {
        $fieldArray = [];
        $collectionData = $collection->getData();
        foreach ($collectionData as $value) {
            $fieldArray[] = $value[$field];
        }
        return $fieldArray;
    }

    /**
     * get encrypted hash data for graph security
     *
     * @param array $data
     * @return void
     */
    public function getChartEncryptedHashData($data)
    {
        return hash('sha256', $data . $this->_deploymentConfigDate);
    }

    /**
     * converts string to date
     *
     * @param string $date
     * @param string $time
     * @return date
     */
    public function stringToDateConversion($date, $time)
    {
        $input = $date.$time;
        $result = date('Y-m-d H:i:s', strtotime($input));
        $newDate = $this->_storeTime->date($result, null, false);
        return $newDate;
    }

    /**
     * get sales collection data
     *
     * @param array $paramData
     * @return Collection
     */
    public function getSalesCollection($paramData)
    {
        $sellerId = '';
        if (array_key_exists('seller_id', $paramData)) {
            $sellerId = $paramData['seller_id'];
        }
        $dateFilter = 0;
        if (array_key_exists(
            'wk_report_date_start',
            $paramData
        ) && array_key_exists(
            'wk_report_date_end',
            $paramData
        )) {
            if (($paramData['wk_report_date_start']!='' &&
                $paramData['wk_report_date_end']!='') ||
                ($paramData['wk_report_date_start']!='' &&
                $paramData['wk_report_date_end'] =='') ||
                ($paramData['wk_report_date_start'] =='' &&
                $paramData['wk_report_date_end'] !='')
            ) {
                $dateFrom = $paramData['wk_report_date_start'];
                $dateFrom = $this->stringToDateConversion($dateFrom, ' 00:00:01');
                $dateTo = $paramData['wk_report_date_end'];
                $dateTo = $this->stringToDateConversion($dateTo, ' 23:59:59');
                $dateFilter = 1;
            }
        }
        $collection = $this->getSalesListCollection(
            $sellerId,
            $paramData
        );

        if ($dateFilter) {
            if ($paramData['wk_report_date_start'] =='') {
                $collection->addFieldToFilter(
                    'created_at',
                    ['lteq' => $dateTo]
                );
            } else {
                $collection->addFieldToFilter(
                    'created_at',
                    ['gteq' => $dateFrom]
                );
                $collection->addFieldToFilter(
                    'created_at',
                    ['lteq' => $dateTo]
                );
            }
        }
        $collection->addFieldToFilter('parent_item_id', ['null' => true]);
        $collection->addFieldToSelect('magequantity')
            ->addFieldToSelect('actual_seller_amount')
            ->addFieldToSelect('created_at');

        $collection->getSelect()
            ->columns('SUM(magequantity) AS total_item_qty')
            ->columns('SUM(actual_seller_amount + total_tax) AS total_seller_amount')
            ->columns('COUNT(DISTINCT order_id) AS total_order_id')
            ->columns('created_at AS order_date')
            ->group('DATE_FORMAT(created_at, "%y-%m-%d")');

        return $collection;
    }

    /**
     * get customer count setting from system config
     *
     * @return void
     */
    public function getCustomerCountSetting()
    {
        return $this->scopeConfig->getValue(
            'wk_mpreportsystem/general_settings/customerdatacount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get product count settings from system config
     *
     * @return void
     */
    public function gettopPtoductCountSetting()
    {
        return $this->scopeConfig->getValue(
            'wk_mpreportsystem/general_settings/productdatacount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get saleslist collection according to filer
     *
     * @param int $sellerId
     * @param array $data
     * @return Collection
     */
    public function getSalesListCollection($sellerId, $data)
    {
        $sellerIdFlag = 0;
        if ($sellerId != '') {
            $sellerIdFlag = 1;
        }
        $productIds = [];
        $categoryFlag = 0;
        $orderIds = [];
        $orderFlag = 0;
        if (array_key_exists('categories', $data) &&
            is_array($data['categories'])
        ) {
            $productIds = $this->getProductIdsByCategoryIds(
                $data['categories']
            );
            $categoryFlag = 1;
        }
        if (array_key_exists(
            'orderstatus',
            $data
        ) && is_array(
            $data['orderstatus']
        )) {
            $orderIds = $this->getOrderIdsByOrderStatus($data['orderstatus']);
            $orderFlag = 1;
        }
        $collection = $this->_marketplaceSalesList
            ->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['neq' => 0]
            );
        if ($sellerIdFlag) {
            $collection->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            );
        }
        if ($categoryFlag) {
            $collection->addFieldToFilter(
                'mageproduct_id',
                ['in' => $productIds]
            );
        }
        if ($orderFlag) {
            $collection->addFieldToFilter(
                'order_id',
                ['in' => $orderIds]
            );
        } else {
            $orderIds = $this->getOrderIdsByOrderStatus(['canceled']);
            if (!empty($orderIds)) {
                $collection->addFieldToFilter(
                    'order_id',
                    ['nin' => $orderIds]
                );
            }
        }
        return $collection;
    }

    /**
     * get region of sales
     *
     * @param int $regionId
     * @return array
     */
    public function getRegionById($regionId)
    {
        $regionModel = $this->_regionFactory->create()->load($regionId);
        return $regionModel;
    }

    /**
     * get current amount as per the current currency
     *
     * @param decimal $amount
     * @return void
     */
    public function getCurrentAmount($amount)
    {
        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $store = $this->_storeManager->getStore()->getStoreId();
        return $this->_priceCurrency->convert($amount, $store, $currency);
    }

    /**
     * Convert price
     *
     * @param integer $amount
     * @param string $store
     * @param string $currency
     * @return void
     */
    public function convertPrice($amount = 0, $store = null, $currency = null)
    {
        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        if ($store == null) {
            $store = $this->_storeManager->getStore()->getStoreId();
        }
        $rate = $this->_priceCurrency
        ->convertAndFormat(
            $amount,
            $includeContainer = true,
            $precision = 2,
            $store,
            $currency
        );
        return $rate;
    }
}
