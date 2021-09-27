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
 * Class RemoveCartItem
 * To remove specific Item from cart
 */
class RemoveCartItem extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            }
            // $itemss = $quote->getAllItems();
            // foreach($itemss as $iteme){
            //     if($iteme->getProductType() == "Membership") {
            //         $quote->removeItem($this->itemId);
            //         $quote->getShippingAddress()->setCollectShippingRates(true);
            //         $quote->collectTotals()->save();
            //         $this->returnArray["success"]=true;
            //         $this->returnArray["message"]="";
            //         $this->returnArray["cartCount"]=0;
            //         return $this->getJsonResponse($this->returnArray);
            //     }
            // }
            $quote->removeItem($this->itemId);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setTriggerRecollect(1);
            $quote->collectTotals()->save();
            if ($quote->getItemsQty()*1 > 0) {
                $totals = [];
                if ($quote->isVirtual()) {
                    $totals = $quote->getBillingAddress()->getTotals();
                } else {
                    $totals = $quote->getShippingAddress()->getTotals();
                }
                $catalogHelper = $this->helperCatalog;
                $checkoutHelper = $this->checkoutHelper;
                $subtotal = [];
                $discount = [];
                $grandtotal = [];
                $shipping = [];
                if (isset($totals["subtotal"])) {
                    $subtotal = $totals["subtotal"];
                    $this->returnArray["subtotal"]["title"] = $subtotal->getTitle();
                    $this->returnArray["subtotal"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($subtotal->getValue())
                    );
                    $this->returnArray["subtotal"]["unformattedValue"] = $subtotal->getValue();
                }
                if (isset($totals["discount"])) {
                    $discount = $totals["discount"];
                    $this->returnArray["discount"]["title"] = $discount->getTitle();
                    $this->returnArray["discount"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($discount->getValue())
                    );
                    $this->returnArray["discount"]["unformattedValue"] = $discount->getValue();
                }
                if (isset($totals["shipping"])) {
                    $shipping = $totals["shipping"];
                    $this->returnArray["shipping"]["title"] = $shipping->getTitle();
                    $this->returnArray["shipping"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($shipping->getValue())
                    );
                    $this->returnArray["shipping"]["unformattedValue"] = $shipping->getValue();
                }
                if (isset($totals["tax"])) {
                    $tax = $totals["tax"];
                    $this->returnArray["tax"]["title"] = $tax->getTitle();
                    $this->returnArray["tax"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($tax->getValue())
                    );
                    $this->returnArray["tax"]["unformattedValue"] = $tax->getValue();
                }
                if (isset($totals["grand_total"])) {
                    $grandtotal = $totals["grand_total"];
                    $this->returnArray["grandtotal"]["title"] = $grandtotal->getTitle();
                    $this->returnArray["grandtotal"]["value"] = $catalogHelper->stripTags(
                        $checkoutHelper->formatPrice($grandtotal->getValue())
                    );
                    $this->returnArray["grandtotal"]["unformattedValue"] = $grandtotal->getValue();
                }
            }
            if ($quote->getItemsQty()*1 == 0) {
                //$this->checkoutSession->setQuoteId($quote->getId());
                $quote->setGift(false);
                $quote->setAmount('');
                $quote->setGiftCode('');
                $quote->save();
            }
            $this->returnArray["success"] = true;
            
            $this->returnArray["cartCount"] = $this->helper->getCartCount($quote);
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->itemId = $this->wholeData["itemId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
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
