<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Block\Adminhtml\Dashboard;

class SalesGraph extends \Magento\Backend\Block\Dashboard\Tab\Amounts
{
    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy
     */
    protected $deliveryboy;
    
    /**
     * @var string
     */
    protected $_template = 'dashboard/graph.phtml';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderResourceCollectionFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $_helperdata;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Backend\Helper\Dashboard\Order $dataHelper
     * @param \Magento\Backend\Helper\Dashboard\Data $dashboardData
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Helper\Dashboard\Order $dataHelper,
        \Magento\Backend\Helper\Dashboard\Data $dashboardData,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory,
        array $data = []
    ) {
       
        $this->deliveryboy = $deliveryboy;
        $this->_helperdata = $helper;
        $this->resource = $resource;
        $this->jsonHelper = $jsonHelper;
        $this->collectionFactory = $collectionFactory;
        $this->deliveryboyOrderResourceCollectionFactory = $deliveryboyOrderResourceCollectionFactory;
        parent::__construct($context, $collectionFactory, $dashboardData, $dataHelper, $data);
    }
    
    /**
     * @return array
     */
    public function getParamValues()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * @return \Magento\Backend\Helper\Dashboard\Data
     */
    public function getDashboardDataHelper()
    {
        return $this->_dashboardData;
    }

     /**
      * Function to fetch daily order list assigned to delivery boy
      *
      * @return array
      */
    public function getDailyOrderList()
    {
        $currDay = date("d");
        $curryear = date("Y");
        $currMonth = date("m");
        $to = $curryear."-".$currMonth."-".$currDay." 23:59:59";
        $from = $curryear."-".$currMonth."-".$currDay." 00:00:00";
        $dailyOrderList = $this->filterCollection($to, $from);

        return $dailyOrderList;
    }

    /**
     * Function to fetch weekly order list assigned to delivery boy
     *
     * @return array
     */
    public function getWeeklyOrderList()
    {
        $curryear = date("Y");
        $currMonth = date("m");
        $currDay = date("d");
        $currWeekDay = date("N");
        $startDate = strtotime("-".($currWeekDay-1)." days", time());
        $prevyear = date("Y", $startDate);
        $prevMonth = date("m", $startDate);
        $prevDay = date("d", $startDate);
        $to = $curryear."-".$currMonth."-".$currDay." 23:59:59";
        $from = $prevyear."-".$prevMonth."-".$prevDay." 00:00:00";
        $weeklyOrderList = $this->filterCollection($to, $from);

        return $weeklyOrderList;
    }

    /**
     * Function to fetch monthly order list assigned to delivery boy
     *
     * @return array
     */
    public function getMonthlyOrderList()
    {
        $currDay = date("d");
        $curryear = date("Y");
        $currMonth = date("m");
        $to = $curryear."-".$currMonth."-".$currDay." 23:59:59";
        $from = $curryear."-".$currMonth."-01 00:00:00";
        $monthlyOrderList = $this->filterCollection($to, $from);
        return $monthlyOrderList;
    }

    /**
     * Function to fetch yearly order list assigned to delivery boy
     *
     * @return array
     */
    public function getYearlyOrderList()
    {
        $curryear = date("Y");
        $to = $curryear."-12-31 23:59:59";
        $from = $curryear."-01-01 00:00:00";
        $yearlyOrderList = $this->filterCollection($to, $from);
        return $yearlyOrderList;
    }

    /**
     * Function to get order count assigned to deliveryboy
     *
     * @param string $to end range of date
     * @param string $from start range of date
     *
     * @return array
     */
    public function filterCollection($to, $from)
    {
        $tableName = $this->resource->getTableName("sales_order_grid");
        $collection = $this->deliveryboyOrderResourceCollectionFactory->create()
            ->getSelect()
            ->join(
                [
                    "salesOrder" => $tableName
                ],
                "main_table.order_id=salesOrder.entity_id",
                [
                    "created_at" => "created_at",
                    "grand_total" => "grand_total"
                ]
            )
            ->where("created_at <= ".$to." AND created_at >= ".$from);
        $orderList = [];
        foreach ($collection as $eachOrder) {
            $oneOrder = [];
            $oneOrder["id"] = $eachOrder->getId();
            $oneOrder["dateTime"] = $eachOrder->getCreatedAt();
            $oneOrder["grandTotal"] = $eachOrder->getGrandTotal();
            $orderList[] = $oneOrder;
        }
        $oneOrder = [];
        $oneOrder["id"] = 1;
        $oneOrder["dateTime"] = date("Y-m-d H:i:s");
        $oneOrder["grandTotal"] = 100;
            
        $orderList[] = $oneOrder;

        return $orderList;
    }

    /**
     * @param string $dateType
     * @return array
     */
    public function getSale($dateType = 'year')
    {
        $data = [];
        if ($dateType == 'year') {
            $data = $this->getYearlyOrderList();
        } elseif ($dateType == 'month') {
            $data = $this->getMonthlyOrderList();
        } elseif ($dateType == 'week') {
            $data = $this->getWeeklyOrderList();
        } elseif ($dateType == 'day') {
            $data = $this->getDailyOrderList();
        }

        return $data;
    }

    /**
     * Generate 6 character radom string from given charset
     *
     * @param string $charset
     * @return string
     */
    public function randString(
        $charset = 'ABC0123456789'
    ) {
        $length = 6;
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[random_int(0, $count - 1)];
        }

        return $str;
    }

    /**
     * Get sales graph url
     *
     * @param string $data
     * @return string
     */
    public function getsalesAmount($data)
    {
        return $this->_helperdata->getSalesAmount($data);
    }
    
    /**
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
