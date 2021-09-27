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

/**
 * Webkul Software.
 */
class UpdateWishlist extends AbstractCustomer
{
    /**
     * Execute Function for UpdateWishlist Class
     *
     * @return json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $wishlist = $this->wishlist->create()->loadByCustomerId($this->customerId, true);
            $updatedItems = 0;
            $wishlistHelper = $this->wishlistHelper;
            if ($this->itemData) {
                foreach ($this->itemData as $eachItem) {
                    $item = $this->wishlistItem->load($eachItem["id"]);
                    if ($item->getWishlistId() != $wishlist->getId()) {
                        continue;
                    }
                    $description = "";
                    if (isset($eachItem["description"])) {
                        $description = (string)$eachItem["description"];
                    }
                    if ($description == $wishlistHelper->defaultCommentString()) {
                        $description = "";
                    } elseif (!strlen($description)) {
                        $description = $item->getDescription();
                    }
                    $qty = null;
                    if (isset($eachItem["qty"])) {
                        $qty = $eachItem["qty"];
                    }
                    if ($qty === null) {
                        $qty = $item->getQty();
                        if (!$qty) {
                            $qty = 1;
                        }
                    } elseif (0 == $qty) {
                        try {
                            $item->delete();
                        } catch (\Exception $e) {
                            $this->returnArray["message"] = __("Can't delete item from wishlist");
                            return $this->getJsonResponse($this->returnArray);
                        }
                    }
                    if (($item->getDescription() == $description) && ($item->getQty() == $qty)) {
                        continue;
                    }
                    try {
                        $item->setDescription($description)->setQty($qty)->save();
                        ++$updatedItems;
                    } catch (\Exception $e) {
                        $this->returnArray["message"] = __(
                            "Can't save description %1",
                            $this->helperCatalog->escapeHtml($description)
                        );
                        return $this->getJsonResponse($this->returnArray);
                    }
                }
            }
            if ($updatedItems) {
                try {
                    $wishlist->save();
                    $wishlistHelper->calculate();
                } catch (\Exception $e) {
                    $this->returnArray["message"] = __("Can't update wishlist");
                    return $this->getJsonResponse($this->returnArray);
                }
            }
            $this->returnArray["success"] = true;
            $this->returnArray["message"] = __("Wishlist updated successfully");
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
     * Function to verify reques
     *
     * @return json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->itemData = $this->wholeData["itemData"] ?? "[]";
            $this->itemData = $this->jsonHelper->jsonDecode($this->itemData);
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
            // Checking customer token //////////////////////////////////////////////
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
