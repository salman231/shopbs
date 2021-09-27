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
 * Class WishlistFromCart
 * To move Item from wishlist to cart.
 */
class WishlistFromCart extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $quote = $this->helper->getCustomerQuote($this->customerId);
            $wishlist = $this->wishlist->loadByCustomerId($this->customerId, true);
            $item = $quote->getItemById($this->itemId);
            $productId = $item->getProductId();
            $buyRequest = $item->getBuyRequest();
            $wishlist->addNewItem($productId, $buyRequest);
            $quote->removeItem($this->itemId);
            $quote->getShippingAddress()->setCollectShippingRates(true);
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
            $wishlist->save();
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Item has been moved to Wishlist");
            $this->returnArray["cartCount"] = $quote->getItemsQty()*1;
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
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->itemId = $this->wholeData["itemId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "As customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
