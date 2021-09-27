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

class DeleteDeliveryboy extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->verifyUsernData();
            if ($this->deliveryboy->load($this->deliveryboyId)->getId() == $this->deliveryboyId
            && $this->deliveryboyId > 0
            ) {
                $tableName = $this->resource->getTableName("sales_order_grid");
                $ordersPending = $this->deliveryboyOrderResourceCollection->create()
                    ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId)
                    ->getSelect()
                    ->join(
                        [
                            "salesOrder"=>$tableName
                        ],
                        "main_table.order_id=salesOrder.entity_id",
                        [
                            "status"=>"status"
                        ]
                    )->where('status in ("pending", "processing")');
                $connection = $this->resource->getConnection();
                $result = $connection->fetchAll($ordersPending);
                if (!empty($result)) {
                    throw new LocalizedException(__("This Deliveryboy still have pending orders."));
                }
                $this->deliveryboyRepository->deleteById($this->deliveryboyId);
                $this->returnArray["success"] = true;
                $this->returnArray["message"] = __("Deliveryboy deleted successfully.");
            } else {
                throw new LocalizedException(__("Invalid delivery boy."));
            }
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
        if ($this->adminCustomerEmail !== $this->deliveryboyHelper->getAdminEmail()) {
            throw new LocalizedException(__("Unauthorized access."));
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "DELETE" && $this->wholeData) {
            $this->storeId = (int)trim($this->wholeData["storeId"] ?? 1);
            $this->deliveryboyId = (int)trim($this->wholeData["deliveryboyId"] ?? 0);
            $this->adminCustomerEmail = trim($this->wholeData["adminCustomerEmail"] ?? "");
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }
}
