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
namespace Webkul\DeliveryBoy\Controller\Api\Deliveryboy;

use Magento\Framework\Exception\LocalizedException;

class ChangeDeliveryboyStatus extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if ($this->deliveryboy->getStatus() == 1) {
                if ($this->onlineStatus == 1) {
                    $this->deliveryboy->setAvailabilityStatus($this->onlineStatus);
                } else {
                    $tableName = $this->resource->getTableName("sales_order_grid");
                    $orderPending = $this->deliveryboyOrderResourceCollection->create()
                        ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
                    $orderPending->getSelect()
                        ->join(
                            [
                                "salesOrder"=>$tableName
                            ],
                            "main_table.order_id=salesOrder.entity_id",
                            [
                                "status"=>"status"
                            ]
                        );
                    $orderPending->addFieldToFilter(
                        "order_status",
                        [
                            "in"=>[
                                "pending",
                                "processing"
                            ]
                        ]
                    );
                    if ($orderPending->getSize() == 0) {
                        $this->deliveryboy->setAvailabilityStatus($this->onlineStatus);
                    } else {
                        throw new LocalizedException(__("You still have pending orders."));
                    }
                }
            } else {
                throw new LocalizedException(__("Requested Deliveryboy is not valid."));
            }
            $this->deliveryboy->save();
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
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->onlineStatus = trim($this->wholeData["onlineStatus"] ?? 0);
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
        } else {
            throw new LocalizedException(__("Invalid Request."));
        }
        if ($this->onlineStatus > 1 || $this->onlineStatus < 0) {
            throw new LocalizedException(__("Invalid delivery boy status."));
        }
        if (!($this->deliveryboyId > 0 &&
             $this->deliveryboy->load($this->deliveryboyId)->getId() == $this->deliveryboyId)
        ) {
            throw new LocalizedException(__("The requested deliveryboy doesn't exist."));
        }
    }
}
