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
use Webkul\DeliveryBoy\Api\Data\OrderInterface as DeliveryboyOrderInterface;
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface as DeliveryboyInterface;

class Orderlist extends AbstractDeliveryboy
{
    const DEFAULT_SORT_FIELD = 'created_at';
    const DEFAULT_SORT_DIRECTION = 'DESC';
    const MAGE_STATUS_PENDING = 'pending';

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected $deliveryboyOrderResourceCollectionInstance;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected $deliveryboyResourceCollectionInstance;

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $now = new \DateTime();
            $this->resetDeliveryboyOrderResourceCollection();
            $this->resetDeliveryboyResourceCollection();
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->validateRequestData();
            $this->verifyUsernData();

            $salesTable = $this->resource->getTableName("sales_order");
            $addressTable = $this->resource->getTableName("sales_order_address");
            $collection = $this->getDeliveryboyOrderResourceCollection();
            $collection->getSelect()
                ->join(
                    [
                        "salesOrder" => $salesTable
                    ],
                    "main_table.order_id=salesOrder.entity_id",
                    [
                        "status" => "status",
                        "entity_id"=>"entity_id",
                        "created_at"=>"created_at",
                        "grand_total"=>"grand_total",
                        "base_currency_code"=>"base_currency_code",
                        "state" => "state",
                        "shipping_address_id"=>"shipping_address_id"
                    ]
                )->join(
                    [
                        "addressTable" => $addressTable
                    ],
                    "salesOrder.shipping_address_id=addressTable.entity_id",
                    [
                        "suffix","suffix",
                        "prefix"=>"prefix",
                        "lastname"=>"lastname",
                        "firstname"=>"firstname",
                        "middlename"=>"middlename"
                    ]
                );
            if ($this->dateFrom) {
                $collection->addFieldToFilter('created_at', ['gteq' => $now->format($this->dateFrom)]);
            }
            if ($this->dateTo) {
                $collection->addFieldToFilter('created_at', ['lteq' => $now->format($this->dateTo)]);
            }
            if ($this->deliveryboyId && $this->adminCustomerEmail) {
                $collection->addFieldToFilter(
                    "deliveryboy_id",
                    $this->deliveryboyId
                );
            }
            if ($this->incrementId) {
                $collection->addFieldToFilter(
                    "main_table.increment_id",
                    [
                        "like" => "%" . $this->incrementId . "%"
                    ]
                );
            }
            if ($this->status) {
                $mageOrderPending = self::MAGE_STATUS_PENDING;
                $mageOrderNew = \Magento\Sales\Model\Order::STATE_NEW;
                $filterValue = implode(',', [$mageOrderNew, $mageOrderPending]);
                if ($this->status === $mageOrderPending) {
                    $collection->addFieldToFilter("order_status", ['in' => $filterValue]);
                } else {
                    $collection->addFieldToFilter("order_status", $this->status);
                }
            }

            $collection->setOrder($this->sortData['by'], $this->sortData['dir']);

            // Applying pagination //////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $collection->getSize();
                $pageSize = $this->deliveryboyHelper->getPageSize();
                $collection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            $orderStatus = [];
            $statusLabel = [];
            $statusCollection = $this->orderStatusCollection
                ->getResourceCollection()
                ->getData();
            foreach ($statusCollection as &$status) {
                $status['label'] = __($status["label"]);
                $statusLabel[$status["status"]] = $status["label"];
                $orderStatus[] = $status;

            }
            $this->returnArray["orderStatus"] = $orderStatus;
            // Creating Order List //////////////////////////////////////////////
            $orderList = [];
            foreach ($collection as $key => $order) {
                $eachOrder = [];
                $eachOrder["id"] = $order->getId();
                $eachOrder["date"] = $this->timezone
                    ->date(new \DateTime($order->getCreatedAt()))->format("M d, Y h:i:s A");
                $mageOrderNew = \Magento\Sales\Model\Order::STATE_NEW;
                $dbOrderStatus = $order->getOrderStatus() === $mageOrderNew
                    ? self::MAGE_STATUS_PENDING
                    : $order->getOrderStatus();
                
                $dbOrderState = $order->getOrderStatus() === $mageOrderNew
                    ? $order->getOrderStatus()
                    : $order->getOrderStatus();
                $eachOrder["status"] = $statusLabel[$dbOrderStatus];
                $eachOrder["shipTo"] = $this->getName($order);
                $eachOrder["state"] = $dbOrderState;
                $eachOrder["orderId"] = (int)$order->getEntityId();
                $eachOrder["orderTotal"] = $this->priceFormatter->format(
                    $order->getGrandTotal(),
                    false,
                    null,
                    null,
                    $order->getBaseCurrencyCode()
                );
                $eachOrder["incrementId"] = $order->getIncrementId();
                $eachOrder["assignStatus"] = $order->getAssignStatus();
                $eachOrder["deliveryboyId"] = (int)$order->getDeliveryboyId();
                $orderList[] = $eachOrder;
            }
            $this->returnArray["orderList"] = $orderList;
            $this->returnArray["dateFormat"] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function verifyUsernData()
    {
        $collection = $this->getDeliveryboyOrderResourceCollection();
        $this->_addBeforeFiltersDeliveryboyOrderResourceCollection($collection);
        $this->appendDeliveryboyList();
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function _addBeforeFiltersDeliveryboyOrderResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection {
        
        if ($this->isDeliveryboy()) {
            $collection->addFieldToFilter(DeliveryboyOrderInterface::DELIVERYBOY_ID, $this->userId);
        }
        if ($this->isValidAssignStatus($this->assignStatus)) {
            $collection->addFieldToFilter(
                DeliveryboyOrderInterface::DELIVERYBOY_ID,
                $this->assignStatus == "0" ? ['eq' => "0"] : ['gt' => 0]
            );
        }
        if ($this->isValidAcceptStatus($this->acceptStatus)) {
            $collection->addFieldToFilter(
                DeliveryboyOrderInterface::ASSIGN_STATUS,
                $this->acceptStatus
            );
        }

        return $collection;
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected function _addBeforeFiltersDeliveryboyResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection {
        return $collection;
    }

    /**
     * @param \Magento\Deliveryboy\Model\Order $object
     * @return string
     */
    public function getName($object)
    {
        $name = "";
        if ($this->config->getAttribute("customer", "prefix")->getIsVisible() && $object->getPrefix()) {
            $name .= $object->getPrefix() . " ";
        }
        $name .= $object->getFirstname();
        if ($this->config->getAttribute("customer", "middlename")->getIsVisible() && $object->getMiddlename()) {
            $name .= " " . $object->getMiddlename();
        }
        $name .= " " . $object->getLastname();
        if ($this->config->getAttribute("customer", "suffix")->getIsVisible() && $object->getSuffix()) {
            $name .= " " . $object->getSuffix();
        }
        return $name;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->userId = trim($this->wholeData["userId"] ?? 0);
            $this->status = trim($this->wholeData["status"] ?? "");
            $this->dateTo = trim($this->wholeData["dateTo"] ?? "");
            $this->dateFrom = trim($this->wholeData["dateFrom"] ?? "");
            $this->sortData = trim($this->wholeData["sortData"] ?? "[]");
            $this->pageNumber = trim($this->wholeData["pageNumber"] ?? 1);
            $this->incrementId = trim($this->wholeData["incrementId"] ?? "");
            $this->acceptStatus = trim($this->wholeData["acceptStatus"] ?? "");
            $this->assignStatus = trim($this->wholeData["assignStatus"] ?? "");
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? "");
            $this->sortData = $this->jsonHelper->jsonDecode($this->sortData);
            $this->sortData['dir'] = isset($this->sortData['dir']) &&
                $this->isSortDirectionValid(strtoupper($this->sortData['dir']))
                    ? strtoupper($this->sortData['dir'])
                    : self::DEFAULT_SORT_DIRECTION;
            $this->sortData['by'] = isset($this->sortData['by']) &&
                $this->isSortFieldValid($this->sortData['by'])
                    ? strtolower($this->sortData['by'])
                    : self::DEFAULT_SORT_FIELD;
            $this->isDeliveryboy = (bool)trim($this->wholeData["isDeliveryboy"] ?? false);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * @param string $sortField
     * @param string $class
     * @return bool
     */
    public function isSortFieldValid(
        string $sortField,
        string $class = \Webkul\DeliveryBoy\Api\Data\OrderInterface::class
    ): bool {
        $orderInterface = new \ReflectionClass($class);
        $fields = array_values($orderInterface->getConstants());

        return in_array($sortField, $fields);
    }

    /**
     * @param string $sortDir
     * @param array $dirs
     * @return bool
     */
    public function isSortDirectionValid(string $sortDir, array $dirs = ['ASC', 'DESC']): bool
    {
        return in_array($sortDir, $dirs);
    }

    /**
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function getDeliveryboyOrderResourceCollection()
        : \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
    {
        if (!($this->deliveryboyOrderResourceCollectionInstance instanceof
            \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection)
        ) {
            $this->deliveryboyOrderResourceCollectionInstance = $this->deliveryboyOrderResourceCollection->create();
        }

        return $this->deliveryboyOrderResourceCollectionInstance;
    }

    /**
     * @param bool $onlyWhere
     * @return void
     */
    protected function resetDeliveryboyOrderResourceCollection(bool $onlyWhere = true)
    {
        if (($deliveryboyOrderResourceCollection = $this->getDeliveryboyOrderResourceCollection())) {
            $select = $deliveryboyOrderResourceCollection
                ->getSelect();
                $select->reset(\Zend_Db_Select::WHERE);
            if (!$onlyWhere) {
                $select->reset(\Zend_Db_Select::COLUMNS);
            }
        }
    }

    /**
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected function getDeliveryboyResourceCollection()
        : \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
    {
        if (!($this->deliveryboyResourceCollectionInstance instanceof
            \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection)
        ) {
            $this->deliveryboyResourceCollectionInstance = $this->deliveryboyResourceCollection->create();
        }

        return $this->deliveryboyResourceCollectionInstance;
    }

    /**
     * @param bool $onlyWhere
     * @return void
     */
    protected function resetDeliveryboyResourceCollection(bool $onlyWhere = true)
    {
        if (($deliveryboyResourceCollection = $this->getDeliveryboyResourceCollection())) {
            $select = $deliveryboyResourceCollection
                ->getSelect();
                $select->reset(\Zend_Db_Select::WHERE);
            if (!$onlyWhere) {
                $select->reset(\Zend_Db_Select::COLUMNS);
            }
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function validateRequestData()
    {
        if (!$this->isDeliveryboy() && !$this->isAdmin()) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * @param string $acceptStatus
     * @return bool
     */
    protected function isValidAcceptStatus($acceptStatus): bool
    {
        return is_numeric($acceptStatus) && in_array($acceptStatus, ["0", "1"]);
    }

    /**
     * @param string $assignStatus
     * @return bool
     */
    protected function isValidAssignStatus($assignStatus): bool
    {
        return is_numeric($assignStatus) && in_array($assignStatus, ["0", "1"]);
    }

    /**
     * @return bool
     */
    protected function shouldAppendDeliveryboyList(): bool
    {
        return $this->isAdmin();
    }

    protected function appendDeliveryboyList()
    {
        if ($this->shouldAppendDeliveryboyList()) {
            $deliveryboyList = [];
            $deliveryboyCollection = $this->getDeliveryboyResourceCollection();
            $this->_addBeforeFiltersDeliveryboyResourceCollection($deliveryboyCollection);
            foreach ($deliveryboyCollection as $each) {
                $eachDeliveryBoy = [];
                $eachDeliveryBoy["id"] = (int)$each->getId();
                $eachDeliveryBoy["name"] = $each->getName();
                $deliveryboyList[] = $eachDeliveryBoy;
            }
            $this->returnArray["deliveryboyList"] = $deliveryboyList;
        }
    }

    /**
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }

    /**
     * @return bool
     */
    protected function isDeliveryboy(): bool
    {
        return $this->isDeliveryboy && ($this->userId > 0);
    }
}
