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

class Deliver extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            if ($this->otp == 0 || !is_numeric($this->otp)) {
                throw new LocalizedException(__("Invalid OTP."));
            }
            if ($this->incrementId == "") {
                throw new LocalizedException(__("Invalid Order."));
            }
            if ($this->deliveryboyId == 0 || !is_numeric($this->deliveryboyId)) {
                throw new LocalizedException(__("Invalid Deliveryboy."));
            }
            $this->deliveryBoy = $this->deliveryboyResourceCollection->create()
                ->addFieldToSelect("id")
                ->addFieldToSelect("status")
                ->addFieldToSelect("availability_status")
                ->addFieldToFilter("id", $this->deliveryboyId)
                ->getFirstItem();
            
            if (!$this->isDeliveryboyAvailable()) {
                throw new LocalizedException(__("Deliveryboy unavailable."));
            }
            $deliveryboyOrderCollection = $this->deliveryboyOrderResourceCollection->create()
                ->addFieldToFilter("increment_id", $this->incrementId)
                ->addFieldToFilter("deliveryboy_id", $this->deliveryboyId);
            $this->_eventManager->dispatch(
                'wk_deliveryboy_assigned_order_collection_apply_filter_event',
                [
                    'deliveryboy_order_collection' => $deliveryboyOrderCollection
                ]
            );
            $deliveryboyOrder = $deliveryboyOrderCollection->getFirstItem();
            if ($deliveryboyOrder->getDeliveryboyId() == $this->deliveryboyId &&
                $deliveryboyOrder->getOtp() == $this->otp
            ) {
                $order = $this->orderFactory->create()->load($deliveryboyOrder->getOrderId());
                if (in_array($order->getState(), $this->deliveryboyHelper->getOrderInvalidStates())) {
                    throw new LocalizedException(
                        __(
                            'Unable to perform the requested operation. The order is in %1 state.',
                            $order->getState()
                        )
                    );
                }
                if ($order->getState() == Order::STATE_NEW || $order->getState() == Order::STATE_PROCESSING ||
                    $order->getState() == Order::STATE_PENDING_PAYMENT
                ) {
                    if (!$order->canInvoice()) {
                        $order->addStatusHistoryComment(__("Order cannot be invoiced."), false);
                        $order->save();
                    }
                    $this->processInvoice($order);
                    $this->processShipment($order);
                    $mageOrderState = $order->getState();
                    $this->setDeliveryboyOrderStatus($deliveryboyOrder, $mageOrderState);
                    $deliveryboyOrder->save();
                    $this->sendNotificationToAdmin($order);
                    $this->sendNotificationToCustomer($order);
                    $this->sendNotificationToDeliveryboy($order);
                }
            } else {
                throw new LocalizedException(__("Invalid OTP."));
            }
            $this->returnArray["message"] = __("Order Delivered Successfully.");
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
        } catch (\Throwable $e) {
            $this->returnArray["message"] = __($e->getMessage());
        }
        
        return $this->getJsonResponse($this->returnArray);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function processInvoice($order)
    {
        if ($order->canInvoice() && !$order->getPayment()->canCapture()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $transaction = $this->transaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transaction->save();
            $this->invoiceSender->send($invoice);
            $order->addStatusHistoryComment(__("Notified customer about invoice #%1.", $invoice->getId()))
                ->setIsCustomerNotified(true)
                ->save();
        } else {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function processShipment(\Magento\Sales\Model\Order $order)
    {
        if ($order->canShip()) {
            $shipment = $this->orderConverter->toShipment($order);
            foreach ($order->getAllItems() as $orderItem) {
                // Check if order item has qty to ship or is virtual
                if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qtyShipped = $orderItem->getQtyToShip();
                // Create shipment item with qty ////////////////////
                $shipmentItem = $this->orderConverter->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                // Add shipment item to shipment ////////////////////
                $shipment->addItem($shipmentItem);
            }
            // Register shipment ////////////////////////////////////
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);
            // Save created shipment and order //////////////////////
            $shipment->save();
            $shipment->getOrder()->save();
            // Send email ///////////////////////////////////////////
            $this->shipmentNotifier->notify($shipment);
            $shipment->save();
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function sendNotificationToAdmin(
        \Magento\Sales\Model\Order $order
    ) {
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
            "body" => __("Order Delivered #%1", $this->incrementId),
            "title" => __("Order update received."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("Order Delivered #%1", $this->incrementId),
            "incrementId" => $this->incrementId,
            "notificationType" => "orderStatus"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $tokenCollection = $this->tokenResourceCollection
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
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function sendNotificationToCustomer(\Magento\Sales\Model\Order $order)
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
            "body" => __("Order Delivered #%1", $this->incrementId),
            "title" => __("Order update received."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("Order Delivered #%1", $this->incrementId),
            "incrementId" => $this->incrementId,
            "notificationType" => "orderStatus"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $tokenCollection = $this->tokenResourceCollection
            ->addFieldToFilter("deliveryboy_id", $order->getCustomerId());
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
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function sendNotificationToDeliveryboy(\Magento\Sales\Model\Order $order)
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
            "body" => __("Order Delivered #%1", $this->incrementId),
            "title" => __("Order update received."),
            "sound" => "default",
            "status" => $order->getStatus(),
            "message" => __("Order Delivered #%1", $this->incrementId),
            "incrementId" => $this->incrementId,
            "notificationType" => "orderStatus"
        ];
        $fields = [
            "data" => $message,
            "priority" => "high",
            "time_to_live" => 30,
            "delay_while_idle" => true,
            "content_available" => true
        ];
        $tokenCollection = $this->tokenResourceCollection
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

    /**
     * @return void
     * @throws LocalizedException
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = trim($this->wholeData["storeId"] ?? 1);
            $this->otp = trim($this->wholeData["otp"] ?? 0);
            $this->incrementId = trim($this->wholeData["incrementId"] ?? 0);
            $this->deliveryboyId = trim($this->wholeData["deliveryboyId"] ?? 0);
        } else {
            throw new LocalizedException(__("Invalid Request"));
        }
        if ($this->otp == 0 || !is_numeric($this->otp)) {
            throw new LocalizedException(__("Invalid OTP."));
        }
        if ($this->incrementId == "") {
            throw new LocalizedException(__("Invalid Order."));
        }
        if (!($this->deliveryboyId > 0 &&
             $this->deliveryboy->load($this->deliveryboyId)->getId() == $this->deliveryboyId)
        ) {
            throw new LocalizedException(__("Invalid delivery boy."));
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

    /**
     * @param \Webkul\DeliveryBoy\Model\Order $deliveryboyOrder
     * @param string $status
     * @return void
     */
    protected function setDeliveryboyOrderStatus(
        \Webkul\DeliveryBoy\Model\Order $deliveryboyOrder,
        string $status
    ) {
         $deliveryboyOrder->setOrderStatus($status);
    }
}
