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
 * Class CheckoutAddressFormData
 * To return the form data of address form at checkout
 */
class CheckoutAddressFormData extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "CHECKOUTADDRESSFORM".$this->storeId.$this->addressId.$this->customerToken;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customer = $this->customerFactory->create();
            if ($this->customerId != 0) {
                $this->customer = $this->customerFactory->create()->load($this->customerId);
                $this->returnArray["lastName"] = $this->customer->getLastname();
                $this->returnArray["firstName"] = $this->customer->getFirstname();
                if ($this->customer->getDefaultBilling() == $this->addressId) {
                    $this->returnArray["addressData"]["isDefaultBilling"] = true;
                } else {
                    $returnArray["addressData"]["isDefaultBilling"] = false;
                }
                if ($this->customer->getDefaultShipping() == $this->addressId) {
                    $this->returnArray["addressData"]["isDefaultShipping"] = true;
                } else {
                    $this->returnArray["addressData"]["isDefaultShipping"] = false;
                }
                $additionalAddress = $this->customer->getAdditionalAddresses();
                foreach ($additionalAddress as $eachAdditionalAddress) {
                    if ($eachAdditionalAddress instanceof \Magento\Framework\DataObject) {
                        $eachAdditionalAddressArray = [];
                        $eachAdditionalAddressArray["value"] = preg_replace(
                            "/(<br\ ?\/?>)+/",
                            ", ",
                            rtrim(
                                preg_replace("/(<br\ ?\/?>)+/", "<br>", preg_replace(
                                    "/[\n\r]/",
                                    "<br>",
                                    $this->helperCatalog->stripTags($eachAdditionalAddress->format("html"))
                                )),
                                "<br>"
                            )
                        );
                        $eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
                        $this->returnArray["address"][] = $eachAdditionalAddressArray;
                    }
                }
            } else {
                $this->returnArray["isGuest"] = true;
            }
            if ($this->addressId != 0) {
                $address = $this->customerAddress->load($this->addressId);
                $addressData = $address->getData();
                foreach ($addressData as $key => $addata) {
                    if ($addata != "") {
                        $this->returnArray["addressData"][$key] = $addata;
                    } else {
                        $this->returnArray["addressData"][$key] = "";
                    }
                }
                $this->returnArray["addressData"]["street"] = $address->getStreet();
            } else {
                $this->returnArray["addressData"] = new \stdClass();
            }
            $this->returnArray["countryData"] = $this->helper->getAddressCountryData();
            $this->returnArray["defaultCountry"] = $this->helper->getConfigData("general/country/default");
            $this->returnArray["streetLineCount"] = $this->addressHelper->getStreetLines();
            $extraAddressData = $this->helper->getAddressFormExtraData($this->customer);
            foreach ($extraAddressData as $key => $value) {
                $this->returnArray[$key] = $value;
            }
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            if (hash("sha256", $encodedData) == $this->eTag) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $this->helper->updateCache($cacheString, $encodedData);
            $this->returnArray["eTag"] = hash("sha256", $encodedData);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
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
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->isGuest = $this->wholeData["isGuest"] ?? false;
            $this->addressId = $this->wholeData["addressId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
