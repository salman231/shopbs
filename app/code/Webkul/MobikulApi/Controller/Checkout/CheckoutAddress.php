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
 * Class CheckoutAddress
 * Get customer addresses on checkout
 */
class CheckoutAddress extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $quote = new \Magento\Framework\DataObject();
            $addressIds = [];
            if ($this->customerId != 0) {
                $customer = $this->customerFactory->create()->load($this->customerId);
                $address = $customer->getPrimaryBillingAddress();
                if ($address instanceof \Magento\Framework\DataObject) {
                    $tempbillingAddress = [];
                    $tempbillingAddress["value"] = $address->format("html");
                    $tempbillingAddress["id"] = $address->getId();
                    if (!in_array($address->getId(), $addressIds)) {
                        $addressIds[] = $address->getId();
                        $this->returnArray["address"][] = $tempbillingAddress;
                    }
                }
                $address = $customer->getPrimaryShippingAddress();
                if ($address instanceof \Magento\Framework\DataObject) {
                    $tempshippingAddress = [];
                    $tempshippingAddress["value"] = $address->format("html");
                    $tempshippingAddress["id"] = $address->getId();
                    if (!in_array($address->getId(), $addressIds)) {
                        $addressIds[] = $address->getId();
                        $this->returnArray["address"][] = $tempshippingAddress;
                    }
                }
                $additionalAddress = $customer->getAdditionalAddresses();
                foreach ($additionalAddress as $eachAdditionalAddress) {
                    if ($eachAdditionalAddress instanceof \Magento\Framework\DataObject) {
                        $eachAdditionalAddressArray = [];
                        $eachAdditionalAddressArray["value"] = $eachAdditionalAddress->format("html");
                        $eachAdditionalAddressArray["id"] = $eachAdditionalAddress->getId();
                        $this->returnArray["address"][] = $eachAdditionalAddressArray;
                    }
                }
                $quote = $this->helper->getCustomerQuote($this->customerId);
                $this->returnArray["lastName"] = $customer->getLastname();
                $this->returnArray["firstName"] = $customer->getFirstname();
                $this->returnArray["middleName"] = ($customer->getMiddlename()===null)? "" : $customer->getMiddlename();
                $this->returnArray["prefixValue"] = ($customer->getPrefix() === null) ? "" : $customer->getPrefix();
                $this->returnArray["suffixValue"] = ($customer->getSuffix() === null) ? "" : $customer->getSuffix();
            }
            if ($this->quoteId != 0) {
                $quote = $this->helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
            }
            if ($quote->getItemsQty()*1 == 0) {
                $this->returnArray["message"] = __("Sorry Something went wrong !!");
                return $this->getJsonResponse($this->returnArray);
            } else {
                $this->returnArray["cartCount"] = $quote->getItemsQty()*1;
            }
            ///////////////////////////// validate minimum amount check /////////////
            $isCheckoutAllowed = $quote->validateMinimumAmount();
            if (!$isCheckoutAllowed) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($this->helper->getConfigData("sales/minimum_order/description"))
                );
            }
            $this->returnArray["isVirtual"] = $quote->isVirtual();
            $this->returnArray["streetLineCount"] = $this->addressHelper->getStreetLines();
            $this->returnArray["defaultCountry"] = $this->helper->getConfigData("general/country/default");
            $showPrefix = $this->helper->getConfigData("customer/address/prefix_show");
            if ($showPrefix == "req") {
                $this->returnArray["isPrefixVisible"] = true;
                $this->returnArray["isPrefixRequired"] = true;
            } elseif ($showPrefix == "opt") {
                $this->returnArray["isPrefixVisible"] = true;
            }
            $prefixOptions = $this->helper->getConfigData("customer/address/prefix_options");
            if ($prefixOptions != "") {
                $this->returnArray["prefixHasOptions"] = true;
                $this->returnArray["prefixOptions"] = explode(";", $prefixOptions);
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
                $this->returnArray["suffixHasOptions"] = true;
                $this->returnArray["suffixOptions"] = explode(";", $suffixOptions);
            }
            $this->returnArray["allowToChooseState"] = (bool)(int)$this->helper->getConfigData(
                "general/region/display_all"
            );
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse(
                $this->returnArray
            );
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
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
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
