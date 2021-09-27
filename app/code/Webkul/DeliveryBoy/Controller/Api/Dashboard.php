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
namespace Webkul\DeliveryBoy\Controller\Api;

use Magento\Framework\Exception\LocalizedException;

class Dashboard extends AbstractDeliveryboy
{
    const ENABLED = 1;
    const MAGE_STATUS_PENDING = 'pending';

    /**
     * @return \Magento\Framework\Conroller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->validateRequestData();
            if ($this->storeId == 0) {
                $this->storeId = $this->websiteManager->create()
                    ->load($this->websiteId)
                    ->getDefaultGroup()
                    ->getDefaultStoreId();
                $this->returnArray["storeId"] = $this->storeId;
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $returnData = $this->verifyUsernData();
            if ($returnData["type"] === "collection") {
                $deliveryboyCollection = $returnData["data"];
                $deliveryboyList = [];
                foreach ($deliveryboyCollection as $each) {
                    $deliveryboyList[] = $this->extractDeliveryboyNecessaryFileldsFromDeliveryboyObject(
                        $each
                    );
                }
                $this->returnArray["deliveryboyList"] = $deliveryboyList;
            } else {
                $deliveryboy = $returnData["data"];
                $this->returnArray["deliveryboyList"][] =
                    $this->extractDeliveryboyNecessaryFileldsFromDeliveryboyObject(
                        $deliveryboy
                    );
            }
            // daily order list /////////////////////////////////////////////////////
            $this->getDailyOrderList();
            // weekly order list ////////////////////////////////////////////////////
            $this->getWeeklyOrderList();
            // monthly order list ///////////////////////////////////////////////////
            $this->getMonthlyOrderList();
            // yearly order list ////////////////////////////////////////////////////
            $this->getYearlyOrderList();

            $this->returnArray['orderStatus'] = $this->getOrderStatus();

            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["storeData"] = $this->helperCatalog->getStoreData(
                $this->websiteId
            );
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function verifyUsernData(): array
    {
        $returnData = [
            "type" => "collection",
            "data" => ""
        ];
        if ($this->isDeliveryboy()) {
            $deliveryboy = $this->deliveryboy->load($this->userId);
            if ($deliveryboy->getId() != $this->userId) {
                throw new LocalizedException(__("Unauthorized access."));
            }
            $returnData["type"] = "object";
            $returnData["data"] = $deliveryboy;
        } else {
            $deliveryboyCollection = $this->deliveryboyResourceCollection
                ->create()
                ->addFieldToFilter("status", self::ENABLED);
            $this->applyFiltersDeliveryboyResourceCollection($deliveryboyCollection);
            $returnData["data"] = $deliveryboyCollection;
        }

        return $returnData;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->userId = trim($this->wholeData["userId"] ?? 0);
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->websiteId = trim($this->wholeData["websiteId"] ?? 1);
            $this->isDeliveryboy = trim($this->wholeData["isDeliveryboy"] ?? false);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * @param int $deliveryboyId
     * @return int
     */
    public function getOrderCount(int $deliveryboyId): int
    {
        $tableName  = $this->resource->getTableName("sales_order_grid");
        $assignedOrderCollection = $this->deliveryboyOrderResourceCollection
            ->create()
            ->addFieldToFilter("deliveryboy_id", $deliveryboyId);
        $this->applyIntermediateFilterOrderCount($assignedOrderCollection);
        $assignedOrderCollection->getSelect()
            ->join(
                [
                    "salesOrder" => $tableName
                ],
                "main_table.order_id=salesOrder.entity_id AND main_table.order_status != 'complete'",
                []
            );
        return count($assignedOrderCollection);
    }

    /**
     * @return void
     */
    public function getDailyOrderList()
    {
        $currDay = date("d");
        $curryear = date("Y");
        $currMonth = date("m");
        $to = $curryear . "-" . $currMonth . "-" . $currDay . " 23:59:59";
        $from = $curryear . "-" . $currMonth . "-" . $currDay . " 00:00:00";
        $this->returnArray["dailyOrderList"] = $this->filterCollection($to, $from);
    }

    /**
     * @return void
     */
    public function getWeeklyOrderList()
    {
        $curryear = date("Y");
        $currMonth = date("m");
        $currDay = date("d");
        $currWeekDay = date("N");
        $startDate = strtotime("-" . ($currWeekDay-1) . " days", time());
        $prevyear = date("Y", $startDate);
        $prevMonth = date("m", $startDate);
        $prevDay = date("d", $startDate);
        $to = $curryear . "-" . $currMonth . "-" . $currDay . " 23:59:59";
        $from = $prevyear . "-" . $prevMonth . "-" . $prevDay . " 00:00:00";
        $this->returnArray["weeklyOrderList"] = $this->filterCollection($to, $from);
    }

    /**
     * @return void
     */
    public function getMonthlyOrderList()
    {
        $currDay = date("d");
        $curryear = date("Y");
        $currMonth = date("m");
        $to = $curryear . "-" . $currMonth . "-" . $currDay . " 23:59:59";
        $from = $curryear . "-" . $currMonth . "-01 00:00:00";
        $this->returnArray["monthlyOrderList"] = $this->filterCollection($to, $from);
    }

    /**
     * @return void
     */
    public function getYearlyOrderList()
    {
        $curryear = date("Y");
        $to = $curryear . "-12-31 23:59:59";
        $from = $curryear . "-01-01 00:00:00";
        $this->returnArray["yearlyOrderList"] = $this->filterCollection($to, $from);
    }

    /**
     * @param string $to
     * @param string $from
     * @return array
     */
    public function filterCollection(string $to, string $from): array
    {
        $tableName = $this->resource->getTableName("sales_order_grid");
        $collection = $this->deliveryboyOrderResourceCollection->create()
            ->join(
                [
                    "salesOrder" => $tableName
                ],
                "main_table.order_id=salesOrder.entity_id",
                [
                    "created_at" => "created_at",
                    "grand_total" => "grand_total",
                    "status" => "status"
                ]
            );
        $collection->addFieldToFilter(
            "created_at",
            [
                "to" => $to,
                "from" => $from,
                "datetime" => true
            ]
        );
        $this->applyIntermediateFilter($collection);
        $orderList = [];
        $mageOrderNew = \Magento\Sales\Model\Order::STATE_NEW;
        foreach ($collection as $eachOrder) {
            $oneOrder = [];
            $oneOrder["id"] = $eachOrder->getId();
            $oneOrder["dateTime"] = $eachOrder->getCreatedAt();
            $oneOrder["grandTotal"] = $eachOrder->getGrandTotal();
            $dbOrderState = $eachOrder->getOrderStatus() === $mageOrderNew
                    ? self::MAGE_STATUS_PENDING
                    : $eachOrder->getOrderStatus();
            $oneOrder["status"] = ucfirst($dbOrderState);
            $orderList[] = $oneOrder;
        }
        return $orderList;
    }

    /**
     * @return array
     */
    protected function getOrderStatus(): array
    {
        return array_values(
            $statusCollection = $this->orderStatusCollection
                ->getResourceCollection()
                ->getData()
        );
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function applyIntermediateFilter(
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
    ) : \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection {
        if ($this->isDeliveryboy()) {
            $collection->addFieldToFilter("deliveryboy_id", ['eq' => $this->userId]);
        }

        return $collection;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function validateRequestData()
    {
        if (!(
        $this->isDeliveryboy()
        || $this->isAdmin())
        ) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected function applyFiltersDeliveryboyResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection {

        return $collection;
    }

    /**
     * @return bool
     */
    public function isDeliveryboy(): bool
    {
        return ($this->isDeliveryboy && ($this->userId > 0));
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @return array
     */
    protected function extractDeliveryboyNecessaryFileldsFromDeliveryboyObject(
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
    ): array {
        return [
            "name" => $deliveryboy->getName(),
            "status" => (bool)$deliveryboy->getAvailabilityStatus(),
            "mobile" => $deliveryboy->getMobileNumber(),
            "latitude" => $deliveryboy->getLatitude(),
            "longitude" => $deliveryboy->getLongitude(),
            "orderCount" => $this->getOrderCount($deliveryboy->getId()),
        ];
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function applyIntermediateFilterOrderCount(
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
    ) : \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection {

        return $collection;
    }
}
