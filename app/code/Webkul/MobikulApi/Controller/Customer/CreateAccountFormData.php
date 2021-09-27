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

class CreateAccountFormData extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "CREATEACCOUNTFORM".$this->storeId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $showPrefix = $this->helper->getConfigData("customer/address/prefix_show");
            if ($showPrefix == "req") {
                $this->returnArray["isPrefixVisible"] = true;
                $this->returnArray["isPrefixRequired"] = true;
            } elseif ($showPrefix == "opt") {
                $this->returnArray["isPrefixVisible"] = true;
            }
            $prefixOptions = $this->helper->getConfigData("customer/address/prefix_options");
            if ($prefixOptions != "") {
                $this->returnArray["prefixOptions"] = explode(";", $prefixOptions);
                $this->returnArray["prefixHasOptions"] = true;
            }
            $showMiddleName = $this->helper->getConfigData("customer/address/middlename_show");
            if ($showMiddleName == 1) {
                $this->returnArray["isMiddlenameVisible"] = true;
            }
            $showSuffix = $this->helper->getConfigData("customer/address/suffix_show");
            if ($showSuffix == "req") {
                $this->returnArray["isSuffixVisible"] = true;
                $this->returnArray["isSuffixRequired"] = true;
            } elseif ($showSuffix == "opt") {
                $this->returnArray["isSuffixVisible"] = true;
            }
            $suffixOptions = $this->helper->getConfigData("customer/address/suffix_options");
            if ($suffixOptions != "") {
                $this->returnArray["suffixOptions"] = explode(";", $suffixOptions);
                $this->returnArray["suffixHasOptions"] = true;
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
            }
            $taxVisible = $this->helper->getConfigData("customer/address/taxvat_show");
            if ($taxVisible == "req") {
                $this->returnArray["isTaxVisible"] = true;
                $this->returnArray["isTaxRequired"] = true;
            } elseif ($taxVisible == "opt") {
                $this->returnArray["isTaxVisible"] = true;
            }
            $genderVisible = $this->helper->getConfigData("customer/address/gender_show");
            if ($genderVisible == "req") {
                $this->returnArray["isGenderVisible"] = true;
                $this->returnArray["isGenderRequired"] = true;
            } elseif ($genderVisible == "opt") {
                $this->returnArray["isGenderVisible"] = true;
            }
            $this->returnArray["dateFormat"] = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
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
            $this->storeId = $this->wholeData["storeId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
