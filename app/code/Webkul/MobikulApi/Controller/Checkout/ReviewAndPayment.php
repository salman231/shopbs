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
 * Class ReviewAndPayment
 * To get order review data and available payment methods
 */
class ReviewAndPayment extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $this->customerSession->setId($this->customerId);
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $store = $this->store;
            $baseCurrency = $store->getBaseCurrencyCode();
            $this->currency = $this->wholeData["currency"] ?? $baseCurrency;
            $store->setCurrentCurrencyCode($this->currency);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
            }
            if ($quote->getItemsQty()*1 == 0) {
                $this->returnArray["message"] = __("Sorry Something went wrong !!");
                return $this->getJsonResponse($this->returnArray);
            } else {
                $this->returnArray["cartCount"] = $quote->getItemsQty()*1;
            }
            // validate minimum amount check ////////////////////////////////////////
            $isCheckoutAllowed = $quote->validateMinimumAmount();
            if (!$isCheckoutAllowed) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($this->helper->getConfigData("sales/minimum_order/description"))
                );
            }
            if ($this->shippingMethod != "") {
                $shippingMethod = $this->shippingMethod;
                $rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
                if (!$rate) {
                    $returnArray["message"] = __("Invalid shipping method.");
                    return $this->getJsonResponse($returnArray);
                }
                $quote->getShippingAddress()->setShippingMethod($this->shippingMethod);
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->collectTotals()->save();
            }
            $orderReviewData = $this->getOrderReviewData($quote);
            $this->getPaymentMethods($quote);
            $this->returnArray["success"] = true;
            $this->returnArray['couponCode'] = $quote->getCouponCode()??'';
            $this->returnArray["currencyCode"] = $this->storeManager->getStore()->getCurrentCurrencyCode();
            $this->returnArray["orderReviewData"] = $orderReviewData;
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
     * Function to veriy the Request
     *
     * @return void|object
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->shippingMethod = $this->wholeData["shippingMethod"] ?? "";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
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
    
    /**
     * Function to populate return array with payment methods
     *
     * @param \Magento\Framework\DataObject $quote quote
     *
     * @return void
     */
    protected function getPaymentMethods($quote)
    {
        $paymentDetails = $this->paymentDetails
            ->setPaymentMethods(
                $this->paymentMethodInterface->getList($quote->getId())
            )->getData();
        foreach ($paymentDetails["payment_methods"] as $method) {
            $oneMethod = [];
            $oneMethod["code"] = $method->getCode();
            $oneMethod["title"] = $method->getTitle();
            $oneMethod["extraInformation"] = "";
            if (in_array($method->getCode(), ["paypal_standard", "paypal_express"])) {
                $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                $config = $this->paypalConfig->setMethod($method->getCode());
                $locale = $this->localeResolver;
                $oneMethod["title"] = "";
                $oneMethod["link"] = $config->getPaymentMarkWhatIsPaypalUrl($locale);
                $oneMethod["imageUrl"] = $config->getPaymentMarkImageUrl($locale->getDefaultLocale());
            } elseif (in_array($method->getCode(), ["paypal_express_bml"])) {
                $oneMethod["extraInformation"] = __("You will be redirected to the PayPal website.");
                $oneMethod["title"] = "";
                $oneMethod["link"] = "https://www.securecheckout.billmelater.com/paycapture-content/fetch".
                "?hash=AU826TU8&content=/bmlweb/ppwpsiw.html";
                $oneMethod["imageUrl"] = "https://www.paypalobjects.com/webstatic/en_US/i/buttons/".
                "ppc-acceptance-medium.png";
            } elseif ($method->getCode() == "checkmo") {
                if ($method->getPayableTo()) {
                    $extraInformationPrefix = __("Make Check payable to:");
                } else {
                    $extraInformationPrefix = __("Send Check to:");
                }
                $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/mailing_address");
                if ($extraInformation == "") {
                    $extraInformation = __(" xxxxxxx");
                }
                $oneMethod["extraInformation"] = $extraInformationPrefix.$extraInformation;
            } elseif ($method->getCode() == "banktransfer") {
                $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/instructions");
                if ($extraInformation == "") {
                    $extraInformation = __("Bank Details are xxxxxxx");
                }
                $oneMethod["extraInformation"] = $extraInformation;
            } elseif ($method->getCode() == "cashondelivery") {
                $extraInformation = $this->helper->getConfigData("payment/".$method->getCode()."/instructions");
                if ($extraInformation == "") {
                    $extraInformation = __("Pay at the time of delivery");
                }
                $oneMethod["extraInformation"] = $extraInformation;
            } elseif (in_array($method->getCode(), ["webkul_stripe", "authorizenet"])) {
                $allowedCc = [];
                $allowedCcTypesString = $method->getConfigData("cctypes");
                $allowedCcTypes = explode(",", $allowedCcTypesString);
                $ccTypes = $this->ccType->toOptionArray();
                $types = [];
                foreach ($ccTypes as $data) {
                    if (isset($data["value"]) && isset($data["label"])) {
                        $types[$data["value"]] = $data["label"];
                    }
                }
                foreach ($allowedCcTypes as $value) {
                    $eachAllowedCc = [];
                    $eachAllowedCc["code"] = $value;
                    $eachAllowedCc["name"] = $types[$value];
                    $allowedCc[] = $eachAllowedCc;
                }
                $extraInformation = $allowedCc;
                $oneMethod["extraInformation"] = $extraInformation;
            } elseif ($method->getCode() == "chargeanywhere") {
                $allowedCc = [];
                $allowedCcTypesString = $method->getConfigData("cctypes");
                $allowedCcTypes = explode(",", $allowedCcTypesString);
                $ccTypes = $this->ccType->toOptionArray();
                $types = [];
                foreach ($ccTypes as $data) {
                    if (isset($data["value"]) && isset($data["label"])) {
                        $types[$data["value"]] = $data["label"];
                    }
                }
                foreach ($allowedCcTypes as $value) {
                    $eachAllowedCc = [];
                    $eachAllowedCc["code"] = $value;
                    $eachAllowedCc["name"] = $types[$value];
                    $allowedCc[] = $eachAllowedCc;
                }
                $extraInformation = $allowedCc;
                $oneMethod["extraInformation"] = $extraInformation;
            }
            $this->returnArray["paymentMethods"][] = $oneMethod;
        }
    }

    public function arrayMerge($arr1, $arr2)
    {
        return array_merge($arr1, $arr2);
    }

    /**
     * Function to get order Review Data
     *
     * @param \Magento\Quote\Model\Quote $quote quote
     *
     * @return array
     */
    public function getOrderReviewData($quote)
    {
        $orderReviewData = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $eachItem = [];
            $eachItem["productName"] = $this->helperCatalog->stripTags($item->getName());
            $customoptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            $result = [];
            if ($customoptions) {
                if (isset($customoptions["options"])) {
                    $result = $this->arrayMerge($result, $customoptions["options"]);
                }
                if (isset($customoptions["additional_options"])) {
                    $result = $this->arrayMerge($result, $customoptions["additional_options"]);
                }
                if (isset($customoptions["attributes_info"])) {
                    $result = $this->arrayMerge($result, $customoptions["attributes_info"]);
                }
            }
            if ($result) {
                foreach ($result as $option) {
                    $eachOption = [];
                    $eachOption["label"] = htmlspecialchars_decode($option["label"]);
                    if (is_array($option["value"])) {
                        $eachOption["value"] = $option["value"];
                    } else {
                        $eachOption["value"][] = $this->helperCatalog->stripTags($option["value"]);
                    }
                    $eachItem["option"][] = $eachOption;
                }
            }
            $eachItem["qty"] = $item->getQty();
            $eachItem["unformattedOriginalPrice"] =round($this->priceCurrencyInterface->convert(
                $item->getProduct()->getPrice(),
                $this->storeId,
                $this->currency
            ), 2);
            $eachItem["originalPrice"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice(
                    $this->priceCurrencyInterface->convert($item->getProduct()->getPrice())
                ),
                $this->storeId,
                $this->currency
            );
            $eachItem["price"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($item->getCalculationPrice())
            );
            $eachItem["subTotal"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($item->getRowTotal())
            );
            $eachItem["thumbNail"] = $this->helperCatalog->getImageUrl(
                $item->getProduct(),
                $this->width/2.5,
                "product_page_image_small"
            );
            $eachItem["dominantColor"] = $this->helper->getDominantColor(
                $this->helper->getDominantColorFilePath(
                    $this->helperCatalog->getImageUrl($item->getProduct(), $this->width/2.5, "product_page_image_small")
                )
            );
            $eachItem["unformattedPrice"] = $item->getCalculationPrice();
            $orderReviewData["items"][]   = $eachItem;
        }
        $address = $quote->getBillingAddress();
        if ($address instanceof \Magento\Framework\DataObject) {
            $this->returnArray["billingAddress"] = $address->format("html");
        }

        if (!$quote->isVirtual()) {
            $address = $quote->getShippingAddress();
            if ($address instanceof \Magento\Framework\DataObject) {
                $this->returnArray["shippingAddress"] = $address->format("html");
            }
            if ($this->shippingMethod = $quote->getShippingAddress()->getShippingDescription()) {
                $this->returnArray["shippingMethod"] = $this->helperCatalog->stripTags($this->shippingMethod);
            }
        }
        $totals = [];
        if ($quote->isVirtual()) {
            $totals = $quote->getBillingAddress()->getTotals();
        } else {
            $totals = $quote->getShippingAddress()->getTotals();
        }
        $orderReviewData["cartTotal"] = 0;
        if (isset($totals["subtotal"])) {
            $subtotal = $totals["subtotal"];
            $orderReviewData["totals"][] = [
                "title" => $subtotal->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($subtotal->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($subtotal->getValue())
                ),
                "unformattedValue" => $subtotal->getValue()
            ];
        }
        if (isset($totals["discount"])) {
            $discount = $totals["discount"];
            $orderReviewData["totals"][] = [
                "title" => $discount->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($discount->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($discount->getValue())
                ),
                "unformattedValue" => $discount->getValue()
            ];
        }
        if (isset($totals["tax"])) {
            $tax = $totals["tax"];
            $orderReviewData["totals"][] = [
                "title" => $tax->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($tax->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($tax->getValue())
                ),
                "unformattedValue" => $tax->getValue()
            ];
        }
        if (isset($totals["shipping"])) {
            $shipping = $totals["shipping"];
            $orderReviewData["totals"][] = [
                "title" => $shipping->getTitle(),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($shipping->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($shipping->getValue())
                ),
                "unformattedValue" => $shipping->getValue()
            ];
        }
        if (isset($totals["grand_total"])) {
            $grandtotal = $totals["grand_total"];
            $orderReviewData["totals"][] = [
                // "title" => $grandtotal->getTitle(),
                "title" => __("Order Total"),
                "value" => $this->helperCatalog->stripTags($this->checkoutHelper->formatPrice($grandtotal->getValue())),
                "formattedValue" => $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($grandtotal->getValue())
                ),
                "unformattedValue" => $grandtotal->getValue()
            ];
            $this->returnArray["cartTotal"] = $this->helperCatalog->stripTags(
                $this->checkoutHelper->formatPrice($grandtotal->getValue())
            );
        }

        if (!empty($orderReviewData)) {
            return $orderReviewData;
        } else {
            return new \stdClass();
        }
    }
}
