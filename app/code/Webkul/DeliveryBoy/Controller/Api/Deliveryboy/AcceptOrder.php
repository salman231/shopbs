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

class AcceptOrder extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->order = $this->orderFactory->create()->loadByIncrementId($this->incrementId);
            if (!$this->order->getId()) {
                throw new LocalizedException(__("Invalid Order."));
            }
            if (!$this->deliveryboyHelper->canAssignOrder($this->order)) {
                throw new LocalizedException(
                    __(
                        'Unable to perform the requested operation. The order is in %1 state.',
                        $this->order->getState()
                    )
                );
            }
            if ($this->deliveryboyId == 0 || !is_numeric($this->deliveryboyId) ||
                $this->deliveryboy->load($this->deliveryboyId)->getId() != $this->deliveryboyId
            ) {
                throw new LocalizedException(__("Invalid Deliveryboy."));
            }
            if (!(bool)$this->assignStatus) {
                if ($this->comment == "") {
                    throw new LocalizedException(__("Comment field is required."));
                }
                if (str_word_count($this->comment) < 5) {
                    throw new LocalizedException(__("Comment should be at least 5 words."));
                }
            }
            $deliveryboyOrderCollection = $this->deliveryboyOrderResourceCollection->create()
                ->addFieldToFilter("increment_id", $this->incrementId)
                ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
            $this->_eventManager->dispatch(
                'wk_deliveryboy_assigned_order_collection_apply_filter_event',
                ['deliveryboy_order_collection' => $deliveryboyOrderCollection]
            );
            $deliveryboyOrder = $deliveryboyOrderCollection->getFirstItem();
            $deliveryboyOrderId = $deliveryboyOrder->getId();
            if ($deliveryboyOrderId) {
                $deliveryBoyOrderUpdating = $this->deliveryboyOrder->create()
                    ->setId($deliveryboyOrderId)
                    ->setAssignStatus($this->assignStatus);
                if ($this->assignStatus == 0) {
                    $deliveryBoyOrderUpdating->setDeliveryboyId(0);
                } else {
                    $deliveryBoyOrderUpdating->setDeliveryboyId($this->deliveryboyId);
                }
                $deliveryBoyOrderUpdating->save();
                // adding comment ///////////////////////////////////////////////////////
                $this->deliveryBoy = $this->deliveryboyResourceCollection->create()
                    ->addFieldToSelect("name")
                    ->addFieldToSelect("id")
                    ->addFieldToFilter("id", $this->deliveryboyId)
                    ->getFirstItem();
                $this->deliveryboyComment->create()
                    ->setIsDeliveryboy(1)
                    ->setComment($this->comment)
                    ->setSenderId($this->deliveryboyId)
                    ->setOrderIncrementId($this->incrementId)
                    ->setDeliveryboyOrderId($deliveryboyOrderId)
                    ->setCommentedBy($this->deliveryBoy->getName())
                    ->setCreatedAt($this->date->gmtDate())
                    ->save();
                $this->sendNotificationToAdmin();
                $this->sendNotificationToDeliveryboy();
                $this->returnArray["message"] = __("Request processed successfully.");
                $this->returnArray["success"] = true;
                $this->emulate->stopEnvironmentEmulation($environment);
            } else {
                $this->returnArray["message"] = __("Invalid delivery boy order.");
            }
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
            $this->comment = trim($this->wholeData["comment"] ?? __("Assigned"));
            $this->incrementId = trim($this->wholeData["incrementId"] ?? "");
            $this->assignStatus = trim($this->wholeData["assignStatus"] ?? 1);
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
    }

    /**
     * @return void
     */
    public function sendNotificationToAdmin()
    {
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $message = [
            "id" => time(),
            "sound" => "default",
            "status" => $this->order->getStatus(),
            "incrementId" => $this->incrementId,
            "notificationType" => "orderAcceptanceStatus"
        ];
        if ($this->assignStatus == '1') {
            $message += [
                "body" => __("Delivery Accepted by %1.", $this->deliveryBoy->getName()),
                "title" => __("Delivery Accepted #%1", $this->incrementId),
                "message" => __("Delivery Accepted by %1.", $this->deliveryBoy->getName()),
            ];
        } else {
            $message += [
                "body" => __("Please check the order status and re-assign to delivery boy."),
                "title" => __("Delivery Declined #%1", $this->incrementId),
                "message" => __("Please check the order status and re-assign to delivery boy."),
            ];
        }
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $tokenCollection = $this->tokenFactory->create()
            ->getCollection()
            ->addFieldToFilter("is_admin", 1);
        foreach ($tokenCollection as $eachToken) {
            $fields["to"] = $eachToken->getToken();
            if ($eachToken->getOs() == "ios") {
                $fields["notification"] = $message;
            }
            $result = $this->operationHelper->send($headers, $fields);
            if (count($result) !== 0) {
                if ($result["success"] == 0 && $result["failure"] == 1) {
                    $eachToken->delete();
                }
            }
        }
    }

    /**
     * @return void
     */
    public function sendNotificationToDeliveryboy()
    {
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
        $message = [
            "id" => time(),
            "sound" => "default",
            "status" => $this->order->getStatus(),
            "incrementId" => $this->incrementId,
            "notificationType" => "orderAcceptanceStatus"
        ];
        if ($this->assignStatus == '1') {
            $message += [
                "body" => __("You have successfully accepted the Delivery for order #", $this->incrementId),
                "title" => __("Delivery Accepted #%1", $this->incrementId),
                "message" => __("You have successfully accepted the Delivery for order #", $this->incrementId),
            ];
        } else {
            $message += [
                "body" => __("Delivery for order #%1 has been rejected successfully.", $this->incrementId),
                "title" => __("Delivery Rejected #%1", $this->incrementId),
                "message" => __("Delivery for order #%1 has been rejected successfully.", $this->incrementId),
            ];
        }
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $tokenCollection = $this->tokenFactory->create()
            ->getCollection()
            ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
        foreach ($tokenCollection as $eachToken) {
            $fields["to"] = $eachToken->getToken();
            if ($eachToken->getOs() == "ios") {
                $fields["notification"] = $message;
            }
            $result = $this->operationHelper->send($headers, $fields);
            if (count($result) !== 0) {
                if ($result["success"] == 0 && $result["failure"] == 1) {
                    $eachToken->delete();
                }
            }
        }
    }
}
