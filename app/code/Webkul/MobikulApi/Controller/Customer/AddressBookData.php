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

namespace Webkul\MobikulApi\Controller\Customer;

class AddressBookData extends AbstractCustomer
{

    public function execute()
    {
        $this->returnArray["billingAddress"]["id"] = 0;
        $this->returnArray["shippingAddress"]["id"] = 0;
        $this->returnArray["billingAddress"]["value"] = __("You have no default billing address in your address book.");
        $this->returnArray["shippingAddress"]["value"] = __(
            "You have no default shipping address in your address book."
        );
        try {
            $addressCount = 0;
            $this->verifyRequest();
            $cacheString = "ADDRESSBOOK".$this->storeId.$this->customerToken;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customer = $this->customerFactory->create()->load($this->customerId);
            $address = $this->customer->getPrimaryBillingAddress();
            if ($address) {
                $this->returnArray["billingAddress"]["value"] = $address->format("html");
                $this->returnArray["billingAddress"]["id"] = $address->getId();
                $this->returnArray["billingAddress"]["addressTitle"] = $address->getAddressTitle();
                $addressCount++;
            }
            $address = $this->customer->getPrimaryShippingAddress();
            if ($address) {
                $this->returnArray["shippingAddress"]["value"] = $address->format("html");
                $this->returnArray["shippingAddress"]["id"] = $address->getId();
                $this->returnArray["shippingAddress"]["addressTitle"] = $address->getAddressTitle();
                $addressCount++;
            }

            if (!$this->forDashboard) {
                $additionalAddress = $this->customer->getAdditionalAddresses();
                foreach ($additionalAddress as $eachAdditionalAddress) {
                    $eachAdditionalAddressArray = [];
                    if ($eachAdditionalAddress) {
                        $eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
                        $eachAdditionalAddressArray["addressTitle"] = $eachAdditionalAddress->getAddressTitle();
                        $eachAdditionalAddressArray["value"] = $eachAdditionalAddress->format("html");
                    } else {
                        $eachAdditionalAddressArray["id"] = 0;
                        $eachAdditionalAddressArray["value"] = __(
                            "You have no other address entries in your address book."
                        );
                    }
                    $addressCount++;
                    $this->returnArray["additionalAddress"][] = $eachAdditionalAddressArray;
                }
            }
            $this->returnArray["addressCount"] = $addressCount;
            $this->returnArray["success"] = true;
            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
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
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
