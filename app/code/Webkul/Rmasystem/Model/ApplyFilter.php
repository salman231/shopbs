<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Model;

use Magento\Framework\Session\SessionManager;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Catalog\Model\ProductRepository;
use Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory;
use Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory as AllRmaCollectionFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class ApplyFilter implements \Webkul\Rmasystem\Api\ApplyFilterInterface
{
  /**
   * @var OrderRepository
   */
    protected $orderRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CollectionFactory
     */
    protected $itemCollectionFactory;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var CollectionFactory
     */
    private $customerSession;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection */
    protected $orderCollection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var AllRmaCollectionFactory
     */
    protected $rmaCollectionFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
     */
    protected $orderShipmentCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\
     */
    protected $orderCollectionFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @param \Webkul\MarketplacePreorder\Helper\Data $preorderHelper
     * @param ItemsRepository $itemsRepository
     * @param MessageInterfaceFactory $preorderItemsFactory
     * @param CollectionFactory $completeCollection
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        CollectionFactory $itemCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
        \Magento\Customer\Model\Session $customerSession,
        ShipmentCollectionFactory $orderShipmentCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        AllRmaCollectionFactory $rmaCollectionFactory,
        OrderFactory $orderFactory,
        SessionManager $session,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->scopeConfig = $scopeInterface;
        $this->customerSession = $customerSession;
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->currency = $currency;
        $this->date = $date;
        $this->session = $session;
        $this->orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Returns selected order detail
     *
     * @api
     * @param string $orderId
     * @param string $price
     * @param string $date
     * @return string.
     */
    public function applyFilter($orderId = 0, $price = null, $date = null)
    {
        $collection = $this->getRmaCollection();
        
        if ($orderId) {
            $collection->addFieldToFilter('increment_id', ['like' => '%'.$orderId.'%']);
        }
        if ($price !== '' && $price !== null) {
            $collection->addFieldToFilter('grand_total', ['eq' => $price]);
        }
        if ($date) {
            $collection->addFieldToFilter('created_at', ['like' => '%'.$date.' %']);
        }
        $orderDetails = [];
        $configData = [];
        $allowedStatus = $this->getAllowedStatus();
        if ($allowedStatus == 'complete') {
            foreach ($collection as $value) {
                $orderId = $value->getOrderId();
                $orderCollection = $this->getOrderCollection($orderId);
                foreach ($orderCollection as $order) {
                    $items = $order->getAllItems();
                    foreach ($items as $item) {
                        $qtyOrdered = $item->getQtyOrdered();
                        $qtyShiped = $item->getQtyShipped();
                        if ($qtyOrdered == $qtyShiped) {
                            $orderDetails[] = [
                              'order_id' => $orderId,
                              'entity_id' => $order->getId(),
                              'increment_id' => $order->getIncrementId(),
                              'date' => date("Y-m-d", $this->getTimestamp($order->getCreatedAt())),
                              'grand_total_format' => $order->formatPrice($order->getGrandTotal()),
                              'grand_total' => $order->getGrandTotal(),
                              'customer_id' => $order->getCustomerId()
                            ];
                        }
                    }
                }
            }
        } else {
            foreach ($collection as $order) {
                $orderDetails[] = [
                  'entity_id' => $order->getId(),
                  'increment_id' => $order->getIncrementId(),
                  'date' => date("Y-m-d", $this->getTimestamp($order->getCreatedAt())),
                  'grand_total_format' => $order->formatPrice($order->getGrandTotal()),
                  'grand_total' => $order->getGrandTotal(),
                  'customer_id' => $order->getCustomerId()
                ];
            }
        }
        $orderDetails = array_unique($orderDetails, SORT_REGULAR);
        $configData['orderDetails'] = $orderDetails;
        $configData['allowedStatus'] = $allowedStatus;
        return json_encode($configData);
    }

    /**
     * @return string
     */
    public function getAllowedStatus()
    {
        return $this->scopeConfig->getValue(
            'rmasystem/parameter/allow_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getRmaCollection()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $sessionData = $this->session->getGuestData();
        $guestEmail = isset($sessionData['email'])?$sessionData['email']:'';

        $allowedStatus =  $this->scopeConfig->getValue(
            'rmasystem/parameter/allow_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $allowedDays = $this->scopeConfig->getValue(
            'rmasystem/parameter/days',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $joinTable = $this->rmaCollectionFactory->create()->getTable('sales_order');
        if ($allowedStatus == 'complete') {
            $collection = $this->orderShipmentCollectionFactory->create();

            $collection->getSelect()->join(
                $joinTable.' as so',
                'main_table.order_id = so.entity_id',
                ['grand_total', 'so.increment_id', 'so.created_at']
            );
            if ($customerId) {
                $collection->where('main_table.customer_id ='.$customerId);
            } else {
                $collection->where('customer_email ='.$guestEmail);
            }

            $collection->addFilterToMap('created_at', 'so.created_at');
            $collection->addFilterToMap('customer_id', 'so.customer_id');
            $collection->addFilterToMap('increment_id', 'so.increment_id');
            if ($customerId) {
                $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);
            } else {
                $collection->addFieldToFilter('customer_email', ['eq' => $guestEmail])
                            ->addFieldToFilter('customer_is_guest', 1);
            }

            $collection->addFieldToFilter('so.status', 'complete');
        } else {
            $collection = $this->orderCollectionFactory->create()
                ->addFieldToFilter(
                    'status',
                    ['neq' => 'canceled']
                )
                ->addFieldToFilter(
                    'status',
                    ['neq' => 'closed']
                );
            if ($customerId) {
                $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);
            } else {
                $collection->addFieldToFilter('customer_email', ['eq' => $guestEmail])
                            ->addFieldToFilter('customer_is_guest', 1);
            }
        }
        if ($allowedDays != '') {
            $todaySecond = time();
            $allowedSeconds = $allowedDays * 86400;
            $pastSecondFromToday = $todaySecond - $allowedSeconds;
            $validFrom = date('Y-m-d H:i:s', $pastSecondFromToday);
            $collection->addFieldToFilter('created_at', ['gteq' => $validFrom]);
        }
        $collection->setOrder('entity_id', 'desc');
        return $collection;
    }

    /**
     * @return array
     */
    public function getOrderCollection($orderId)
    {
        return $this->orderCollectionFactory->create()
                    ->addFieldToFilter(
                        'entity_id',
                        $orderId
                    );
    }

    /**
     * @param Decimal $price
     *
     * @return string
     */
    public function getCurrency($price)
    {
        return $currency = $this->currency->format($price);
    }

    /**
     * @param Decimal $price
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * @param  String Date
     *
     * @return String Timestamp
     */
    public function getTimestamp($date)
    {
        return $date = $this->date->timestamp($date);
    }
}
