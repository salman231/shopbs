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
namespace Webkul\Rmasystem\Block\Guest;

use Magento\Framework\Session\SessionManager;
use Webkul\Rmasystem\Helper\Filter;
use Webkul\Rmasystem\Model\ResourceModel\Allrma\CollectionFactory as AllRmaCollectionFactory;
use Webkul\Rmasystem\Model\ResourceModel\Reason\CollectionFactory as ReasonCollectionFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * Guest new RMA block.
 */
class Newrma extends \Magento\Framework\View\Element\Template
{
      /**
       * @var Magento\Framework\App\Config\ScopeConfigInterface;
       */
    protected $scopeConfig;

      /**
       * @var \Magento\Directory\Model\Currency
       */
    protected $_currency;

      /**
       * @var \Magento\Framework\Stdlib\DateTime\DateTime
       */
    protected $_date;

      /**
       * @var \Webkul\Rmasystem\Model\ResourceModel\Resion\CollectionFactory
       */
    protected $_regionCollectionFactory;

      /**
       * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory
       */
    protected $_orderShipmentCollectionFactory;

      /**
       * @var \Magento\Sales\Model\ResourceModel\Order\
       */
    protected $_orderCollectionFactory;

      /**
       * @var \Magento\Customer\Model\Session
       */
    protected $_customerSession;

      /**
       * @var Session
       */
    protected $session;

      /**
       * @var Webkul\Rmasystem\Helper\Filter
       */
    protected $_filterSorting;

      /**
       * @var \Magento\Sales\Model\Order\Config
       */
    protected $_orderConfig;

      /**
       * @var \Magento\Framework\Data\Form\FormKey
       */
    protected $_formKey;

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

    protected $rma;

    protected $_objectManager = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                      $context
     * @param \Magento\Customer\Model\Session                                       $customerSession
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory   $orderShipmentCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory            $orderCollectionFactory
     * @param \Magento\Directory\Model\CurrencyFactory                              $currencyFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                           $date
     * @param \Magento\Framework\View\Element\FormKey                               $formKey
     * @param \Magento\Framework\ObjectManagerInterface                             $objectManager
     * @param \Webkul\Rmasystem\Model\ResourceModel\Reason\CollectionFactory        $regionCollectionFactory
     * @param SessionManager                                                        $session
     * @param Filter                                                                $filterSorting
     * @param array                                                                 $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        ShipmentCollectionFactory $orderShipmentCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        ReasonCollectionFactory $reasonCollectionFactory,
        AllRmaCollectionFactory $rmaCollectionFactory,
        OrderFactory $orderFactory,
        SessionManager $session,
        \Magento\Framework\Registry $coreRegistry,
        Filter $filterSorting,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_orderShipmentCollectionFactory = $orderShipmentCollectionFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_currency = $currency;
        $this->_date = $date;
        $this->_regionCollectionFactory = $reasonCollectionFactory;
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->session = $session;
        $this->coreRegistry = $coreRegistry;
        $this->_filterSorting = $filterSorting;
        $this->scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('New RMA'));
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getRmaCollection()
    {
        $sessionData = $this->_objectManager->create(
            \Magento\Framework\Session\SessionManager::class
        )->getGuestData();

        if (empty($sessionData)) {
            $sessionData = $this->coreRegistry->registry('guest_data');
        }
        $allowedStatus = $this->getAllowedStatus();
        if (!$this->rma) {
            if ($allowedStatus == 'complete') {
                $arrayOfOrderId = [];
                $orderCollection = $this->_orderCollectionFactory->create()
                                    ->addFieldToFilter('customer_is_guest', 1)
                                    ->addFieldToFilter('customer_email', $sessionData['email']);
                foreach ($orderCollection as $value) {
                    $arrayOfOrderId[] = $value->getEntityId();
                }
                $joinTable = $this->rmaCollectionFactory->create()->getTable('sales_order');
                $collection = $this->_orderShipmentCollectionFactory->create();
                $collection->getSelect()->join(
                    $joinTable.' as so',
                    'main_table.order_id = so.entity_id',
                    ['grand_total', 'so.increment_id', 'so.created_at']
                );
                $collection->addFilterToMap('created_at', 'so.created_at');
                $collection->addFilterToMap('customer_id', 'so.customer_id');
                $collection->addFilterToMap('increment_id', 'so.increment_id');
                $collection->addFieldToFilter('so.status', 'complete');
                $collection->addFieldToFilter('order_id', ['in' => $arrayOfOrderId]);
            } else {
                $collection = $this->_orderCollectionFactory->create()
                    ->addFieldToFilter('customer_email', $sessionData['email'])
                    ->addFieldToFilter('customer_is_guest', 1);
            }
            $allowedDays = $this->scopeConfig->getValue(
                'rmasystem/parameter/days',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($allowedDays != '') {
                $todaysSecond = time();
                $allowedSeconds = $allowedDays * 86400;
                $pastSecondFromToday = $todaysSecond - $allowedSeconds;
                $validFrom = date('Y-m-d H:i:s', $pastSecondFromToday);
                $collection->addFieldToFilter('created_at', ['gteq' => $validFrom]);
            }
            $collection->setOrder('entity_id', 'desc');

            $this->rma = $collection;
        }

        return $this->rma;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getRmaCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'webkul.rmasystem.new.pager'
            )->setCollection(
                $this->getRmaCollection()
            );
            $this->setChild('pager', $pager);
            $this->getRmaCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return array
     */
    public function getSortingSession()
    {
        return $this->filterSorting->getNewRmaSortingSession();
    }
    /**
     * @return array
     */
    public function getGuestFilterData()
    {
        return $this->filterSorting->getNewGuestRmaFilterSession();
    }
    /**
     * @return array
     */
    public function getOrderCollection($orderId)
    {
        return $this->_orderCollectionFactory->create()
                    ->addFieldToFilter(
                        'entity_id',
                        $orderId
                    );
    }
    /**
     * @return array
     */
    public function getRegionCollection()
    {
        return $this->_regionCollectionFactory->create()
                    ->addFieldToFilter(
                        'status',
                        1
                    );
    }
    /**
     * [loadOrderModel description].
     *
     * @param int $orderId
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrderModel($orderId)
    {
        return $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
    }
    /**
     * @param Decimal $price
     *
     * @return [type] [description]
     */
    public function getCurrency($price)
    {
        return $currency = $this->_currency->format($price);
    }
    /**
     * @param Decimal $price
     *
     * @return [type] [description]
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }
    /**
     * @param  String Date
     *
     * @return String Timestamp
     */
    public function getTimestamp($date)
    {
        return $date = $this->_date->timestamp($date);
    }

    /**
     *
     */
    public function getAllowedStatus()
    {
        return $this->scopeConfig->getValue(
            'rmasystem/parameter/allow_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     *
     */
    public function getPolicy()
    {
        return $this->_escaper->escapeHtml($this->scopeConfig->getValue(
            'rmasystem/parameter/returnpolicy',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Create Customer orders data for RMA
     * @return array
     */
    public function getRmaConfigData()
    {
        $collection = $this->getRmaCollection();
        $orderDetails = [];
        $configData = [];
        $allowedStatus = $this->getAllowedStatus();
        if ($allowedStatus == 'complete') {
            foreach ($collection as $value) {
                $orderId = $value->getOrderId();
                $orderCollection = $this->getOrderCollection($orderId);

                foreach ($orderCollection as $order) {
                    if ($order->isCanceled()) {
                        continue;
                    }
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
                if ($order->isCanceled()) {
                    continue;
                }
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
        $configData['filterData'] = $this->getFilterData();

        return $configData;
    }
}
