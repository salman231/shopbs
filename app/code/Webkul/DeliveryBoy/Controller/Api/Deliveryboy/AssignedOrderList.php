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

use Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy;
use Magento\Framework\Exception\LocalizedException;

class AssignedOrderList extends AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $orderCollection = $this->deliveryboyOrderResourceCollection->create();
            $this->applyFiltersDeliveryboyOrderResourceCollection($orderCollection);
            $this->_eventManager->dispatch(
                'wk_deliveryboy_assigned_order_collection_apply_filter_event',
                ['deliveryboy_order_collection' => $orderCollection]
            );
            // Creating Order List //////////////////////////////////////////
            $orderList = [];
            foreach ($orderCollection as $order) {
                $orderList[] = $order->getIncrementId();
            }
            $this->returnArray["orderList"] = $orderList;
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
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
            if (!($this->deliveryboyId > 0 &&
                $this->deliveryboy->load($this->deliveryboyId)->getId() == $this->deliveryboyId
            )) {
                throw new LocalizedException(__("Invalid delivery boy."));
            }
        } else {
            throw new LocalizedException(__("Invalid Request."));
        }
    }

    /**
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
     * @return \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection
     */
    protected function applyFiltersDeliveryboyOrderResourceCollection(
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection $collection
    ): \Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection {

        $collection
            ->addFieldToFilter("assign_status", ["nin" => ["0", "1"]])
            ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId)
            ->addFieldToFilter(
                "order_status",
                [
                    'neq' => \Magento\Sales\Model\Order::STATE_COMPLETE
                ]
            );

        return $collection;
    }
}
