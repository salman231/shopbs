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
namespace Webkul\DeliveryBoy\Controller\Api\Admin;

use Magento\Framework\Exception\LocalizedException;

class GetDeliveryboyList extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->authorizeRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $deliveryboyCollection = $this->verifyUsernData();
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $deliveryboyCollection->getSize();
                $pageSize = $this->deliveryboyHelper->getPageSize();
                $deliveryboyCollection->setPageSize($pageSize)
                    ->setCurPage($this->pageNumber);
            }
            $Iconheight = $IconWidth = 144 * $this->mFactor;
            $deliveryboyList = [];
            foreach ($deliveryboyCollection as $each) {
                $eachDeliveryboy = [];
                $eachDeliveryboy["id"] = $each->getId();
                $eachDeliveryboy["name"] = $each->getName();
                $eachDeliveryboy["status"] = $each->getStatus();
                $eachDeliveryboy["orderCount"] = $this->getOrderCount($each->getId());
                $newUrl = "";
                $basePath = $this->baseDir . DIRECTORY_SEPARATOR . $each->getImage();
                try {
                    if ($this->fileDriver->isFile($basePath)) {
                        $newPath = $this->baseDir . DIRECTORY_SEPARATOR . "deliveryboyresized" . DIRECTORY_SEPARATOR .
                            $IconWidth . "x" . $Iconheight . DIRECTORY_SEPARATOR . $each->getImage();
                        $this->helperCatalog->resizeNCache($basePath, $newPath, $IconWidth, $Iconheight);
                        $newUrl = $this->deliveryboyHelper->getUrl("media") . "deliveryboyresized"
                            . DIRECTORY_SEPARATOR .
                            $IconWidth . "x" . $Iconheight . DIRECTORY_SEPARATOR . $each->getImage();
                    }
                } catch (\Throwable $t) {
                    $this->logger->debug($t->getMessage());
                }
                $eachDeliveryboy["avatar"] = $newUrl;
                $eachDeliveryboy["availabilityStatus"] = (bool)$each->getAvailabilityStatus();
                $ratings = $this->ratingCollection->create()
                    ->addFieldToSelect("deliveryboy_id")
                    ->addFieldToSelect("rating")
                    ->addFieldToSelect("status")
                    ->addFieldToFilter("status", 1)
                    ->addFieldToFilter("deliveryboy_id", $eachDeliveryboy["id"]);
                $ratings->getSelect()
                    ->columns(
                        [
                            "avg" => new \Zend_Db_Expr("AVG(rating)")
                        ]
                    );
                foreach ($ratings as $each) {
                    $eachDeliveryboy["rating"] = number_format($each->getAvg(), 1);
                }
                $deliveryboyList[] = $eachDeliveryboy;
            }
            $this->returnArray["success"] = true;
            $this->returnArray["deliveryboyList"] = $deliveryboyList;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     * @throws LocalizedException
     */
    protected function verifyUsernData(): \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
    {
        $deliveryboyCollection = $this->deliveryboyResourceCollection->create();
        $this->applyFiltersDeliveryboyResourceCollection($deliveryboyCollection);
        $this->setSortOrderDeliveryboyResourceCollection($deliveryboyCollection);

        return $deliveryboyCollection;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->mFactor = trim($this->wholeData["mFactor"] ?? 1);
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->pageNumber = trim((int)($this->wholeData["pageNumber"] ?? 1));
            $this->purpose = trim($this->wholeData["purpose"] ?? "creation");
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
        $tableName = $this->resource->getTableName("sales_order_grid");
        $assignedOrderCollection = $this->deliveryboyOrderResourceCollection
            ->create()
            ->addFieldToFilter("assign_status", 1)
            ->addFieldToFilter("deliveryboy_id", $deliveryboyId);
        $this->applyIntermediateFilterOrderCount($assignedOrderCollection);
        $assignedOrderCollection->getSelect()
            ->join(
                ["salesOrder" => $tableName],
                "main_table.order_id=salesOrder.entity_id AND salesOrder.status != 'complete'",
                []
            );
            
        return count($assignedOrderCollection);
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

    /**
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return $this->adminCustomerEmail === $this->deliveryboyHelper->getAdminEmail();
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected function applyFiltersDeliveryboyResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection {
        if ($this->purpose == "assignment") {
            $collection->addFieldToFilter("status", 1);
            $collection->addFieldToFilter("availability_status", 1);
        }
        return $collection;
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection
     */
    protected function setSortOrderDeliveryboyResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection {
        $collection->setOrder("created_at", "ASC");
        
        return $collection;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function authorizeRequest()
    {
        if (!$this->isAdmin()) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }
}
