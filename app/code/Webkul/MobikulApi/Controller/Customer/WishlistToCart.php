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

use Magento\Framework\Exception\LocalizedException;

class WishlistToCart extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $item = $this->wishlistItem->load($this->itemId);
            if (!$item || !$item->getId()) {
                throw new LocalizedException(__("Item id is invalid"));
            }
            $wishlist = $this->wishlistProvider->load($item->getWishlistId());
            if ($wishlist->getCustomerId() != $this->customerId) {
                throw new LocalizedException(__("Invalid data."));
            }
            $options = $this->wishlistItemOption->getCollection()->addItemFilter([$this->itemId]);
            $item->setQty($this->qty);
            $item->setOptions($options->getOptionsByItem($this->itemId));
            $buyRequest = $this->productHelper->addParamsToBuyRequest(
                ["item"=>$this->itemId, "qty"=>[$this->itemId=>$this->qty]],
                ["current_config"=>$item->getBuyRequest()]
            );
            $item->mergeBuyRequest($buyRequest);
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
            $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($quoteId);
            $this->cart->setQuote($quote);
            try {
                $item->addToCart($this->cart, true);
            } catch (\Exception $e) {
                $message = __($e->getMessage());
                if (strlen($message) > 88) {
                    $message = substr($message, 0, 88)."...";
                }
                $this->returnArray["message"] = $message;
                return $this->getJsonResponse($this->returnArray);
            }
            $this->cart->save()->getQuote()->collectTotals()->save();
            $wishlist->save();
            $this->returnArray["cartCount"] = $this->helper->getCartCount($this->cart->getQuote());
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Product(s) has successfully moved to cart.");
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
            $this->qty = $this->wholeData["qty"] ?? 1;
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->itemId = $this->wholeData["itemId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
