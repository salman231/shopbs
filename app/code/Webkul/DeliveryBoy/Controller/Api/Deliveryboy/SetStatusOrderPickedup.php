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

use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;
use Webkul\DeliveryBoy\Model\Deliveryboy as Deliveryboy;

class SetStatusOrderPickedup extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            
            $this->deliveryBoy = $this->deliveryboyResourceCollection->create()
                ->addFieldToSelect("name")
                ->addFieldToSelect("id")
                ->addFieldToSelect("mobile_number")
                ->addFieldToSelect("status")
                ->addFieldToSelect("availability_status")
                ->addFieldToFilter("id", $this->deliveryboyId)
                ->getFirstItem();
            
            $this->order = $this->orderFactory->create()->loadByIncrementId($this->incrementId);
            if (!$this->deliveryboyHelper->canAssignOrder($this->order)) {
                throw new LocalizedException(
                    __(
                        'Unable to perform the requested operation. The order is in %1 state.',
                        $this->order->getState()
                    )
                );
            }
            $orderState = Order::STATE_PROCESSING;
            $this->order->setState($orderState)->setStatus($orderState);
            $this->order->save();
            
            $deliveryboyOrderCollection = $this->deliveryboyOrderResourceCollection->create()
                ->addFieldToFilter("increment_id", $this->incrementId)
                ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
            $this->_eventManager->dispatch(
                'wk_deliveryboy_assigned_order_collection_apply_filter_event',
                ['deliveryboy_order_collection' => $deliveryboyOrderCollection]
            );
            $deliveryboyOrder = $deliveryboyOrderCollection->getFirstItem();
            
            if (!$this->deliveryBoy->getId() || $deliveryboyOrder->getDeliveryboyId() != $this->deliveryboyId) {
                throw new LocalizedException(__("Invalid Deliveryboy."));
            }
            if (!$this->isDeliveryboyAvailable()) {
                throw new LocalizedException(__("Deliveryboy unavailable."));
            }
            $deliveryboyOrder->setPicked(1)
                ->setOrderStatus($orderState)->save();
            $this->deliveryboyComment->create()
                ->setComment(__("Order picked up from store."))
                ->setSenderId($this->deliveryboyId)
                ->setIsDeliveryboy(1)
                ->setOrderIncrementId($this->incrementId)
                ->setDeliveryboyOrderId($deliveryboyOrder->getId())
                ->setCommentedBy($this->deliveryBoy->getName())
                ->setCreatedAt($this->date->gmtDate())
                ->save();
            
            $this->sendEmail();
            $this->sendNotificationToCustomer();
            $this->sendNotificationToAdmin();
            $this->sendNotificationToDeliveryboy();
            
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Order picked up from store.");
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }

        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * Verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->incrementId = trim($this->wholeData["incrementId"] ?? "");
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
        if (empty($this->incrementId)) {
            throw new LocalizedException(__("Invalid Order."));
        }
        if (!($this->deliveryboyId > 0 &&
            $this->deliveryboy->load($this->deliveryboyId)->getId() == $this->deliveryboyId)
        ) {
            throw new LocalizedException(__("Invalid Deliveryboy."));
        }
    }

    /**
     * @return void
     */
    public function sendNotificationToCustomer()
    {
        $message = [
            "id" => time(),
            "body" => __("Your Order has been picked up by %1", $this->deliveryBoy->getName()),
            "title" => __("Order update received."),
            "sound" => "default",
            "status" => $this->order->getStatus(),
            "message" => __("Your Order has been picked up by %1", $this->deliveryBoy->getName()),
            "incrementId" => $this->order->getIncrementId(),
            "notificationType" => "orderPickedUp"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $tokenCollection = $this->tokenFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter("deliveryboy_id", $this->order->getCustomerId());
        $this->sendNotification($fields, $message, $tokenCollection);
    }

    /**
     * @return void
     */
    public function sendNotificationToDeliveryboy()
    {
        $message = [
            "id" => time(),
            "body" => __("Order #%1 has been picked up successfully.", $this->order->getIncrementId()),
            "title" => __("Order update received."),
            "sound" => "default",
            "status" => $this->order->getStatus(),
            "message" => __("Order #%1 has been picked up successfully.", $this->order->getIncrementId()),
            "incrementId" => $this->order->getIncrementId(),
            "notificationType" => "orderPickedUp"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $tokenCollection = $this->tokenFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
        $this->sendNotification($fields, $message, $tokenCollection);
    }

    /**
     * @return void
     */
    public function sendNotificationToAdmin()
    {
        $message = [
            "id" => time(),
            "body" => __("Order picked up by %1", $this->deliveryBoy->getName()),
            "title" => __("Order update received"),
            "sound" => "default",
            "status" => $this->order->getStatus(),
            "message" => __("Order picked up by %1", $this->deliveryBoy->getName()),
            "incrementId" => $this->order->getIncrementId(),
            "notificationType" => "orderStatus"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $tokenCollection = $this->tokenFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter("is_admin", 1);
        $this->sendNotification($fields, $message, $tokenCollection);
    }

    /**
     * @param array $fields
     * @param array $message
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenCollection
     * @return void
     */
    protected function sendNotification(
        array $fields,
        array $message,
        \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenCollection
    ) {
        $authKey = $this->deliveryboyHelper->getFcmApiKey();
        if (empty($authKey)) {
            return ;
        }
        $headers = [
            "Authorization: key=" . $authKey,
            "Content-Type: application/json",
        ];
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
    public function sendEmail()
    {
        try {
            $templateVariables = [];
            $templateVariables["orderDate"] = $this->deliveryboyHelper->formatDateTimeCurrentLocale(
                $this->order->getCreatedAt()
            );
            $templateVariables["orderStatus"] = $this->order->getStatus();
            $templateVariables["customerName"] = $this->order->getCustomerFirstname()
                . " " . $this->order->getCustomerLastname();
            $templateVariables["deliveryboyName"] = $this->deliveryBoy->getName();
            $templateVariables["orderIncrementId"] = $this->order->getIncrementId();
            $templateVariables["deliveryboyContact"] = $this->deliveryBoy->getMobileNumber();
            $this->inlineTranslation->suspend();
            $senderInfo = [
                "name"  => "Admin",
                "email" => $this->deliveryboyHelper->getGeneralEmail()
            ];
            $receiverInfo = [
                "name"  => $templateVariables["customerName"],
                "email" => $this->order->getCustomerEmail()
            ];
            $template = "deliveryboy_order_pickup";
            $template = $this->transportBuilder->setTemplateIdentifier($template)
                ->setTemplateOptions(
                    [
                        "area"  => \Magento\Framework\App\Area::AREA_FRONTEND,
                        "store" => $this->storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($templateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo["email"], $receiverInfo["name"]);
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Throwable $t) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function isDeliveryboyAvailable()
    {
        if (!($this->deliveryBoy->getStatus() == Deliveryboy::STATUS_ENABLED) ||
        !($this->deliveryBoy->getAvailabilityStatus() == Deliveryboy::STATUS_ENABLED)
        ) {
            return false;
        }

        return true;
    }
}
