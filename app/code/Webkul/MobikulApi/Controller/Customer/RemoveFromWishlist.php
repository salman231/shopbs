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

class RemoveFromWishlist extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $item = $this->wishlistItem->load($this->itemId);
            if (!$item->getId()) {
                $this->returnArray["alreadyDeleted"] = true;
                return $this->getJsonResponse($this->returnArray);
            }
            $error = false;
            $wishlist = $this->wishlistProvider->loadByCustomerId($this->customerId, true);
            if (!$wishlist) {
                $error = true;
            }
            $item->delete();
            $wishlist->save();
            if ($error) {
                $this->returnArray["message"] = __("An error occurred while deleting the item from wishlist.");
                return $this->getJsonResponse($this->returnArray);
            }
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Item successfully deleted from wishlist.");
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
        if ($this->getRequest()->getMethod() == "DELETE" && $this->wholeData) {
            $this->itemId = $this->wholeData["itemId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
