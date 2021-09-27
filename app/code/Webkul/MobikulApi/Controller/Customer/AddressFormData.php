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

class AddressFormData extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "ADDRESSFORM".$this->storeId.$this->addressId.$this->customerToken;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customer = $this->customerFactory->create();
            if ($this->customerId != 0) {
                $this->customer = $this->customerFactory->create()->load($this->customerId);
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
            $showCompany = $this->helper->getConfigData("customer/address/company_show");
            if ($showCompany == "req") {
                $this->returnArray["isCompanyVisible"] = true;
                $this->returnArray["isCompanyRequired"] = true;
            } elseif ($showCompany == "opt") {
                $this->returnArray["isCompanyVisible"] = true;
                $this->returnArray["isCompanyRequired"] = false;
            } else {
                $this->returnArray["isCompanyVisible"] = false;
            }
        
            $showTelephone = $this->helper->getConfigData("customer/address/telephone_show");
            if ($showTelephone == "req") {
                $this->returnArray["isTelephoneVisible"] = true;
                $this->returnArray["isTelephoneRequired"] = true;
            } elseif ($showTelephone == "opt") {
                $this->returnArray["isTelephoneVisible"] = true;
                $this->returnArray["isTelephoneRequired"] = false;
            } else {
                $this->returnArray["isTelephoneVisible"] = false;
            }
        
            $showFax = $this->helper->getConfigData("customer/address/fax_show");
            if ($showFax == "req") {
                $this->returnArray["isFaxVisible"] = true;
                $this->returnArray["isFaxRequired"] = true;
            } elseif ($showFax == "opt") {
                $this->returnArray["isFaxVisible"] = true;
                $this->returnArray["isFaxRequired"] = false;
            } else {
                $this->returnArray["isFaxVisible"] = false;
            }
            $showPrefix = $this->helper->getConfigData("customer/address/prefix_show");
            if ($showPrefix == "req") {
                $this->returnArray["isPrefixVisible"] = true;
                $this->returnArray["isPrefixRequired"] = true;
                $prefixOptions = $this->helper->getConfigData("customer/address/prefix_options");
                if ($prefixOptions == "") {
                    $this->returnArray["prefixOptions"] = explode(";", $prefixOptions);
                    $this->returnArray["prefixHasOptions"] = true;
                }
            } elseif ($showPrefix == "opt") {
                $this->returnArray["isPrefixVisible"] = true;
                $this->returnArray["isPrefixRequired"] = false;
                $prefixOptions = $this->helper->getConfigData("customer/address/prefix_options");
                if ($prefixOptions == "") {
                    $this->returnArray["prefixOptions"] = explode(";", $prefixOptions);
                    $this->returnArray["prefixHasOptions"] = true;
                }
            } else {
                $this->returnArray["isPrefixVisible"] = false;
            }
            $showMiddleName = $this->helper->getConfigData("customer/address/middlename_show");
            if ($showMiddleName == 1) {
                $this->returnArray["isMiddlenameVisible"] = true;
            } else {
                $this->returnArray["isMiddlenameVisible"] = false;
            }
            $showSuffix = $this->helper->getConfigData("customer/address/suffix_show");
            if ($showSuffix == "req") {
                $this->returnArray["isSuffixVisible"] = true;
                $this->returnArray["isSuffixRequired"] = true;
                $suffixOptions = $this->helper->getConfigData("customer/address/suffix_options");
                if ($suffixOptions = "") {
                    $this->returnArray["suffixOptions"] = explode(";", $suffixOptions);
                    $this->returnArray["suffixHasOptions"] = true;
                }
            } elseif ($showSuffix == "opt") {
                $this->returnArray["isSuffixVisible"] = true;
                if ($suffixOptions = "") {
                    $this->returnArray["suffixOptions"] = explode(";", $suffixOptions);
                    $this->returnArray["suffixHasOptions"] = true;
                }
                $this->returnArray["isSuffixRequired"] = false;
            } else {
                $this->returnArray["isSuffixVisible"] = false;
            }
            $mobileStatus = $this->helper->getConfigData("mobikul/configuration/enable_mobile_login");
            if ($mobileStatus == 1) {
                $this->returnArray["isMobileVisible"] = true;
                $this->returnArray["isMobileRequired"] = true;
            }
            $dobVisible = $this->helper->getConfigData("customer/address/dob_show");
            if ($dobVisible == "req") {
                $this->returnArray["isDOBVisible"] = true;
                $this->returnArray["isDOBRequired"] = true;
            } elseif ($dobVisible == "opt") {
                $this->returnArray["isDOBVisible"] = true;
                $this->returnArray["isDOBRequired"] = false;
            } else {
                $this->returnArray["isDOBVisible"] = false;
            }
            $taxVisible = $this->helper->getConfigData("customer/address/taxvat_show");
            if ($taxVisible == "req") {
                $this->returnArray["isTaxVisible"] = true;
                $this->returnArray["isTaxRequired"] = true;
            } elseif ($taxVisible == "opt") {
                $this->returnArray["isTaxVisible"] = true;
                $this->returnArray["isTaxRequired"] = false;
            } else {
                $this->returnArray["isTaxVisible"] = false;
            }
            $genderVisible = $this->helper->getConfigData("customer/address/gender_show");
            if ($genderVisible == "req") {
                $this->returnArray["isGenderVisible"] = true;
                $this->returnArray["isGenderRequired"] = true;
            } elseif ($genderVisible == "opt") {
                $this->returnArray["isGenderVisible"] = true;
                $this->returnArray["isGenderRequired"] = false;
            } else {
                $this->returnArray["isGenderVisible"] = true;
            }
            $this->returnArray["isAddressTitleVisible"] = true;
            $this->returnArray["isAddressTitleRequired"] = false;
            $this->returnArray["countryData"] = $this->helper->getAddressCountryData();
            $this->returnArray["lastName"] = $this->customer->getLastname();
            $this->returnArray["firstName"] = $this->customer->getFirstname();
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
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->addressId = $this->wholeData["addressId"] ?? 0;
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
