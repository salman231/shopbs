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

class AccountInfoData extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "ACCOUNTINFO".$this->storeId.$this->customerToken;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->customer = $this->customerFactory->create()->load($this->customerId);
            $this->returnArray["email"] = $this->customer->getEmail();
            $this->returnArray["lastName"] = $this->customer->getLastname();
            $this->returnArray["firstName"] = $this->customer->getFirstname();
            $showPrefix = $this->helper->getConfigData("customer/address/prefix_show");
            if ($showPrefix == "req") {
                $this->returnArray["isPrefixVisible"] = true;
                $this->returnArray["isPrefixRequired"] = true;
                $this->returnArray["prefixValue"] = $this->customer
                    ->getPrefix() === null ? "" : $this->customer->getPrefix();
            } elseif ($showPrefix == "opt") {
                $this->returnArray["isPrefixVisible"] = true;
                $this->returnArray["isPrefixRequired"] = false;
                $this->returnArray["prefixValue"] = $this->customer
                    ->getPrefix() === null ? "" : $this->customer->getPrefix();
            }
            $prefixOptions = $this->helper->getConfigData("customer/address/prefix_options");
            if ($prefixOptions == "") {
                $this->returnArray["prefixHasOptions"] = true;
                $this->returnArray["prefixOptions"] = explode(";", $prefixOptions);
            }
            $showMiddleName = $this->helper->getConfigData("customer/address/middlename_show");
            if ($showMiddleName == 1) {
                $this->returnArray["middleName"] = $this->customer
                    ->getMiddlename() === null ? "" : $this->customer->getMiddlename();
                $this->returnArray["isMiddlenameVisible"] = true;
            }
            $showSuffix = $this->helper->getConfigData("customer/address/suffix_show");
            if ($showSuffix == "req") {
                $this->returnArray["isSuffixVisible"] = true;
                $this->returnArray["isSuffixRequired"] = true;
                $this->returnArray["suffixValue"] = $this->customer
                    ->getSuffix() === null ? "" : $this->customer->getSuffix();
            } elseif ($showSuffix == "opt") {
                $this->returnArray["isSuffixVisible"] = true;
                $this->returnArray["isSuffixRequired"] = false;
                $this->returnArray["suffixValue"] = $this->customer
                    ->getSuffix() === null ? "" : $this->customer->getSuffix();
            }
            $suffixOptions = $this->helper->getConfigData("customer/address/suffix_options");
            if ($suffixOptions == "") {
                $this->returnArray["suffixHasOptions"] = true;
                $this->returnArray["suffixOptions"] = explode(";", $suffixOptions);
            }
            $mobileStatus = $this->helper->getConfigData("mobikul/configuration/enable_mobile_login");
            if ($mobileStatus == 1) {
                $this->returnArray["isMobileVisible"] = true;
                $this->returnArray["isMobileRequired"] = true;
            }
            $dOBVisible = $this->helper->getConfigData("customer/address/dob_show");
            if ($dOBVisible == "req") {
                $this->returnArray["isDOBVisible"] = true;
                $this->returnArray["isDOBRequired"] = true;
                $this->returnArray["DOBValue"] = $this->customer->getDob() === null ? "" : $this->customer->getDob();
            } elseif ($dOBVisible == "opt") {
                $this->returnArray["isDOBVisible"] = true;
                $this->returnArray["isDOBRequired"] = false;
                $this->returnArray["DOBValue"] = $this->customer->getDob() === null ? "" : $this->customer->getDob();
            }
            $taxVisible = $this->helper->getConfigData("customer/address/taxvat_show");
            if ($taxVisible == "req") {
                $this->returnArray["isTaxVisible"] = true;
                $this->returnArray["isTaxRequired"] = true;
                $this->returnArray["taxValue"] = $this->customer
                    ->getTaxvat() === null ? "" : $this->customer->getTaxvat();
            } elseif ($taxVisible == "opt") {
                $this->returnArray["isTaxVisible"] = true;
                $this->returnArray["isTaxRequired"] = false;
                $this->returnArray["taxValue"] = $this->customer
                    ->getTaxvat() === null ? "" : $this->customer->getTaxvat();
            }
            $genderVisible = $this->helper->getConfigData("customer/address/gender_show");
            if ($genderVisible == "req") {
                $this->returnArray["isGenderVisible"] = true;
                $this->returnArray["isGenderRequired"] = true;
                $this->returnArray["genderValue"] = $this->customer
                    ->getGender() === null ? 0 : $this->customer->getGender();
            } elseif ($genderVisible == "opt") {
                $this->returnArray["isGenderVisible"] = true;
                $this->returnArray["isTaxRequired"] = false;
                $this->returnArray["genderValue"] = $this->customer
                    ->getGender() === null ? 0 : $this->customer->getGender();
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
            $this->returnArray["dateFormat"] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $this->returnArray["success"] = true;
            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            if (hash("sha256", $encodedData) == $this->eTag) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $this->helper->updateCache($cacheString, $encodedData);
            $this->returnArray["eTag"] = hash("sha256", $encodedData);
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
