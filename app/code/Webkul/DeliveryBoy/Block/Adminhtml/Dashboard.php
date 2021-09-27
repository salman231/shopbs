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
namespace Webkul\DeliveryBoy\Block\Adminhtml;
 
class Dashboard extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     */
    protected $deliveryboyResourceCollectionFactory;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderResourceCollectionFactory;

    /**
     * @param \Webkul\DeliveryBoy\Helper\Data $helperData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     *        $deliveryboyOrderResourceCollectionFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     *        $deliveryboyResourceCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Data $helperData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyResourceCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        
        $this->helperData = $helperData;
        $this->timezone = $timezone;
        $this->resource = $resource;
        $this->_coreRegistry = $registry;
        $this->orderFactory = $orderFactory;
        $this->deliveryboyResourceCollectionFactory = $deliveryboyResourceCollectionFactory;
        $this->deliveryboyOrderResourceCollectionFactory = $deliveryboyOrderResourceCollectionFactory;
    }

    /**
     * @return array
     */
    public function getDeliveryBoyCollection()
    {
        $deliveryboyCollection = $this->deliveryboyResourceCollectionFactory
            ->create()
            ->addFieldToFilter("status", 1);
        $deliveryboyList = [];
        foreach ($deliveryboyCollection as $each) {
            $deliveryboyList[] = [
                "name" => $each->getName(),
                "status" => (bool)$each->getAvailabilityStatus(),
                "mobile" => $each->getMobileNumber(),
                "latitude" => $each->getLatitude(),
                "longitude" => $each->getLongitude(),
                "orderCount" => $this->getOrderCount($each->getId())
            ];
        }
        return $deliveryboyList;
    }

    /**
     * @param int $deliveryboyId
     * @return int
     */
    public function getOrderCount($deliveryboyId)
    {
        $tableName  = $this->resource->getTableName("sales_order_grid");
        $assignedOrderCollection = $this->deliveryboyOrderResourceCollectionFactory
            ->create()
            ->addFieldToFilter("deliveryboy_id", $deliveryboyId);
        $assignedOrderCollection->getSelect()
            ->join(
                ["salesOrder" => $tableName],
                "main_table.order_id=salesOrder.entity_id AND salesOrder.status != 'complete'",
                []
            );
        return count($assignedOrderCollection);
    }

     /**
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
     * @param string $to
     * @param string $from
     * @return array
     */
    public function filterCollection($to, $from)
    {
        $tableName = $this->resource->getTableName("sales_order_grid");
        $collection = $this->deliveryboyOrderResourceCollectionFactory->create()
            ->getSelect()
            ->join(
                ["salesOrder" => $tableName],
                "main_table.order_id=salesOrder.entity_id",
                ["created_at" => "created_at", "grand_total" => "grand_total"]
            )->where("created_at <= ".$to." AND created_at >= ".$from);
        $orderList = [];
        foreach ($collection as $eachOrder) {
            $oneOrder = [];
            $oneOrder["id"] = $eachOrder->getId();
            $oneOrder["dateTime"] = $this->deliveryboyHelper->formatDateTimeCurrentLocale($eachOrder->getCreatedAt());
            $oneOrder["grandTotal"] = $eachOrder->getGrandTotal();
            $orderList[] = $oneOrder;
        }
        return $orderList;
    }
}
