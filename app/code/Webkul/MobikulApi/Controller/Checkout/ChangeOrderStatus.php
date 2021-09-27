<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Checkout;

/**
 * Class ChangeOrderStatus
 * change the status of order
 */
class ChangeOrderStatus extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $order = $this->orderFactory->create()->loadByIncrementId($this->incrementId);
            $payment = $order->getPayment();
            $payment->setTransactionId($this->confirm["response"]["id"])
                ->setPreparedMessage("status : ".$this->confirm["response"]["state"])
                ->setShouldCloseParentTransaction(true)
                ->setIsTransactionClosed(0)
                ->registerCaptureNotification($order->getGrandTotal());
            $order->save();
            $state = "";
            if ($this->status == 0) {
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->save();
                $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
            } else {
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)
                    ->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED)
                    ->save();
                $state = \Magento\Sales\Model\Order::STATE_CANCELED;
            }
            if ($order->canInvoice()) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $transactionSave = $this->dbTransaction->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();
                $this->invoiceSender->send($invoice);
            }
            $comment = "status :".$this->confirm["response"]["state"]."<br>";
            $comment .= "transaction id :".$this->confirm["response"]["id"]."<br>";
            $comment .= "date :".$this->confirm["response"]["create_time"]."<br>";
            $comment .= "from :".$this->confirm["client"]["product_name"]."<br>";
            $order->addStatusHistoryComment($comment)->setIsCustomerNotified(true);
            $order->save();
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->status = $this->wholeData["status"] ?? 0;
            $this->confirm = $this->wholeData["confirm"] ?? "{}";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->incrementId = $this->wholeData["incrementId"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->confirm = $this->jsonHelper->jsonDecode($this->confirm);
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerToken && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
