<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Customer;

class ReOrder extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $order = $this->order->loadByIncrementId($this->incrementId);
            if ($order->getCustomerId() != $this->customerId) {
                $this->returnArray["message"] = __("Invalid Order.");
                return $this->getJsonResponse($this->returnArray);
            }
            $debugBackTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $outOfStockItems = [];
            $quote = $this->helper->getCustomerQuote($this->customerId);
            $quoteId = $quote->getId();
            if ($quote->getId() < 0 || !$quoteId) {
                $quote = $this->quoteFactory->create()
                    ->setStoreId($this->storeId)
                    ->setIsActive(true)
                    ->setIsMultiShipping(false)
                    ->save();
                $quoteId = (int) $quote->getId();
                $customer = $this->customerRepositoryInterface->getById($this->customerId);
                $quote->assignCustomer($customer);
                $quote->setCustomer($customer);
                $quote->getBillingAddress();
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals()->save();
            }
            $cart = $this->cartFactory->create()->setQuote($quote);
            $items = $order->getItemsCollection();
            $this->checkoutSession->setQuoteId($quote->getId());
            foreach ($items as $item) {
                $cart->addOrderItem($item);
            }
            
            foreach ($quote->getAllItems() as $quoteItem) {
                if ($quoteItem->getProductId() == $this->checkoutSession->getLastAddedProductId()) {
                    $quoteItem->setQty($quoteItem->getData("qty"))->save(); //To update the quantity in quote item
                }
            }
            $cart->save();
            
            $this->returnArray["cartCount"] = $this->helper->getCartCount($quote);
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Product(s) has been added to cart.");
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Verify Request function to verify the request
     *
     * @return void|jSon
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->incrementId = $this->wholeData["incrementId"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
