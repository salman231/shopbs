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
 * Class CartDetails
 * To return the details about item and totals in cart.
 */
class CartDetails extends AbstractCheckout
{
    /**
     * Execute function for class CartDetails
     *
     * @return json|void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Setting currency /////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($this->currency);
            if ($this->helper->getConfigData("sales/minimum_order/active")) {
                $this->returnArray["minimumAmount"] = (int)$this->helper->getConfigData("sales/minimum_order/amount");
                $this->returnArray["minimumFormattedAmount"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice($this->returnArray["minimumAmount"])
                );
            } else {
                $this->returnArray["minimumAmount"] = 0;
                $this->returnArray["minimumFormattedAmount"] = $this->helperCatalog->stripTags(
                    $this->checkoutHelper->formatPrice(0)
                );
            }
            $this->returnArray["showThreshold"] = (bool)$this->helper->getConfigData(
                "cataloginventory/options/product_stock_status"
            );
            $this->quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $this->quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $this->quote = $this->helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
            }
            $this->returnArray["canGuestCheckoutDownloadable"] = false;
            if ($this->helper->getConfigData("catalog/downloadable/disable_guest_checkout") == 0) {
                $this->returnArray["canGuestCheckoutDownloadable"] = true;
            }
            $this->returnArray["allowMultipleShipping"] = (bool)$this->helper->getConfigData(
                "multishipping/options/checkout_multiple"
            );
            if ($this->customerId != 0 || $this->quoteId != 0) {
                $this->quote->getShippingAddress()->setCollectShippingRates(true);
                $this->quote->collectTotals()->save();
                // getting cart items ///////////////////////////////////////////////
                $cartItemData = $this->getCartItemList();
                $this->returnArray["items"] = $cartItemData["items"];
                $this->returnArray["totalCount"] = $cartItemData["totalCount"];
                if ($this->quote->getCouponCode() !== null) {
                    $this->returnArray["couponCode"] = $this->quote->getCouponCode();
                }
                if ($this->quote->getGiftCode() !== null) {
                    $this->returnArray["giftCode"] = $this->quote->getGiftCode();
                    $this->returnArray["giftcode_discount"] = $this->quote->getBasefee();
                    //$this->returnArray["giftData"]["giftAmount"] = $this->quote->getAmount();
                }
                if ($this->quote->getIsVirtual()) {
                    $this->returnArray["isVirtual"] = (bool)$this->quote->getIsVirtual();
                }
                // getting cross sell list //////////////////////////////////////////
                $this->returnArray["crossSellList"] = $this->getCrossSellList();
                if ($this->checkoutHelper->isAllowedGuestCheckout($this->quote)) {
                    $this->returnArray["isAllowedGuestCheckout"] = $this->checkoutHelper->isAllowedGuestCheckout(
                        $this->quote
                    );
                }
                $this->returnArray["cartCount"] = $this->helper->getCartCount($this->quote);
                // getting totals details ///////////////////////////////////////////
                if ($this->returnArray["cartCount"] > 0) {
                    foreach ($this->getTotalsData() as $key => $eachTotal) {
                        $this->returnArray['totalsData'][] = $eachTotal;
                    }
                }
                // validate minimum amount check ////////////////////////////////////
                $isCheckoutAllowed = $this->quote->validateMinimumAmount();
                if (!$isCheckoutAllowed) {
                    $this->returnArray["isCheckoutAllowed"] = false;
                    $this->returnArray["descriptionMessage"] = $this->helper->getConfigData(
                        "sales/minimum_order/description"
                    );
                } elseif ($this->quote->getHasError()) {
                    $this->returnArray["isCheckoutAllowed"] = false;
                } else {
                    $this->returnArray["isCheckoutAllowed"] = true;
                }
            }
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function to verify request
     *
     * @return json|void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->currency = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
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
     * Function to get cart Itme List
     *
     * @return array
     */
    protected function getCartItemList()
    {
        $this->items = [];
        $cartItemData = [];
        $this->cartProductIds[] = "";
        $itemCollection = $this->itemCollectionFactory->create()->setQuote($this->quote);
        $itemCollection->addFieldToFilter("parent_item_id", ["null" => true]);
        if ($this->pageNumber >= 1) {
            $cartItemData["totalCount"] = $itemCollection->getSize();
            $pageSize = $this->helper->getConfigData("checkout/cart/number_items_to_display_pager");
            $itemCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
        }
        foreach ($itemCollection as $item) {
            $product = $item->getProduct();
            
            //   $customOptions = $item->getOptionByCode('additional_options');
            //   $row = $customOptions->getData();
            //   $newrow=[];
            //   $newrow=$row['value'];
            //   $roww=json_decode($newrow,TRUE);
            //   $durationlabel=$roww['duration']['label'];
            //   $durationvalue=$roww['duration']['value'];
            //   $durationunitlabel=$roww['duration_unit']['label'];
            //   $durationunitvalue=$roww['duration_unit']['value'];
            //   echo $durationlabel." = ".$durationvalue.", ";
            //   echo $durationunitlabel." = ".$durationunitvalue;

            //$product_options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
            //$customer_plan = unserialize($product_options['info_buyRequest']['duration_option']);
            //$duration = $customer_plan['duration'];
            //$durationUnit = $customer_plan['duration_unit'];
            //echo $duration." = ".$durationUnit;
            // $options = $item->getProductOptions();
            // print_r($options['info_buyRequest']);
            // exit();

            // $option = $item->getOptionByCode('info_buyRequest');
            // print_r($option['duration_option']);
            // exit();

            // $options = $item->getProductOptions();
            // var_dump($options['info_buyRequest']['duration_option']);
            //exit();
            if ($product) {
                $this->cartProductIds[] = $product->getId();
            }
            $eachItem = [];
            $eachItem["image"] = $this->helperCatalog->getImageUrl(
                $product,
                $this->width/2.5,
                "product_page_image_small"
            );
            $eachItem["thresholdQty"] = $this->helper->getConfigData("cataloginventory/options/stock_threshold_qty");
            $eachItem["remainingQty"] = $this->stockState->getStockQty(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
            $eachItem["dominantColor"] = $this->helper->getDominantColor(
                $this->helper->getDominantColorFilePath(
                    $this->helperCatalog->getImageUrl($product, $this->width/2.5, "product_page_image_small")
                )
            );
            $eachItem["name"] = htmlspecialchars_decode($item->getName());
            $eachItem["canMoveToWishlist"] = $item->getProduct()->isVisibleInSiteVisibility();
            $options = $product->getTypeInstance(true)->getOrderOptions($product);
            if ($product->getTypeId() == "configurable") {
                $skuProduct = $this->productFactory->create()
                ->getCollection()
                ->addFieldToSelect("entity_id")
                ->addStoreFilter($this->storeId)
                ->addFieldToFilter("sku", $item->getSku())
                ->getFirstItem();

                $skuProduct = $this->productFactory->create()->load($skuProduct["entity_id"]);
                $eachItem["image"] =$this->helperCatalog->getImageUrl(
                    $skuProduct,
                    $this->width/2.5,
                    "product_page_image_small"
                );
                
                $configurableOptions = $options["attributes_info"];
                foreach ($configurableOptions as $configurableOption) {
                    $eachConfigurableOption = [];
                    $eachConfigurableOption["optionId"] = $configurableOption["option_id"];
                    $eachConfigurableOption["label"] = $configurableOption["label"];
                    $eachConfigurableOption["valueIds"][] =htmlspecialchars_decode($configurableOption["option_value"]);
                    $eachConfigurableOption["value"][] = htmlspecialchars_decode($configurableOption["value"]);
                    $eachItem["options"][] = $eachConfigurableOption;
                }
            }
            if ($product->getTypeId() == "bundle") {
                $bundleOptions = $options["bundle_options"];
                foreach ($bundleOptions as $bundleOption) {
                    $eachBundleOption = [];
                    $eachBundleOption["optionId"] = $bundleOption["option_id"];
                    $eachBundleOption["label"] = $bundleOption["label"];
                    foreach ($bundleOption["value"] as $bundleOptionValue) {
                        $price = 0;
                        if ($bundleOptionValue["price"] > 0) {
                            $price = $bundleOptionValue["price"]/$bundleOptionValue["qty"];
                        }
                        $price = $this->helperCatalog->stripTags($this->priceHelper->currency($price));
                        $eachBundleOptionValue = $bundleOptionValue["qty"]." x ".$bundleOptionValue["title"]." ".$price;
                        $eachBundleOption["valueIds"] = ["qty"=>$bundleOptionValue["qty"]];
                        $eachBundleOption["value"][] = htmlspecialchars_decode($eachBundleOptionValue);
                    }
                    $eachItem["options"][] = $eachBundleOption;
                }
            }
            if ($product->getTypeId() == "downloadable") {
                $links = $this->downloadableConfiguration->getLinks($item);
                if (count($links) > 0) {
                    $downloadOption = [];
                    $titles = [];
                    foreach ($links as $linkId) {
                        $titles[] = htmlspecialchars_decode($linkId->getTitle(), ENT_QUOTES);
                    }
                    $downloadOption["label"] = $this->downloadableConfiguration->getLinksTitle($product);
                    $downloadOption["value"] = $titles;
                    $eachItem["options"][] = $downloadOption;
                }
            }
            if (isset($options["options"])) {
                $customOptions = $options["options"];
                foreach ($customOptions as $customOption) {
                    $eachCustomOption = [];
                    $eachCustomOption["label"] = $customOption["label"];
                    $eachCustomOption["value"][] = htmlspecialchars_decode($customOption["print_value"]);
                    $eachItem["options"][] = $eachCustomOption;
                }
            }
            $eachItem["id"] = $item->getId();
            $eachItem["sku"] = $this->helperCatalog->stripTags($item->getSku());
            $eachItem["qty"] = $item->getQty()*1;
            $eachItem["typeId"] = $item->getProductType();
            $price = $product->getPrice();
            if ($product->getTypeId() == "configurable") {
                $regularPrice = $product->getPriceInfo()->getPrice("regular_price");
                $price = $regularPrice->getAmount()->getBaseAmount();
            } elseif (empty($price)) {
                $price = 0.0;
            }
            //$this->returnArray["message"] = $this->helper->getCurrencyConvertedFormattedPrice($item->getRowTotal());
            // $this->helper->printLog($this->returnArray);
            //return $this->getJsonResponse($this->returnArray);
            //print_r($item->getRowTotal());echo "Parbat";
            $eachItem["price"] = $item->getCalculationPrice();
            $eachItem["subTotal"] = $this->helper->getCurrencyConvertedFormattedPrice($item->getRowTotal());
            $eachItem["finalPrice"] = $item->getCalculationPrice();
            $eachItem["formattedPrice"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $item->getCalculationPrice()
            );
            $eachItem["formattedFinalPrice"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $item->getCalculationPrice()
            );

            if ($this->helperCatalog->getIfTaxIncludeInPrice() == 2) {
                $eachItem["price"] = $this->catalogHelper->getTaxPrice($product, $price);
                $eachItem["subTotal"] = $this->helper->getCurrencyConvertedFormattedPrice($item->getRowTotalInclTax());
                $eachItem["finalPrice"] = $item->getPriceInclTax();
                $eachItem["formattedPrice"] = $this->helperCatalog->stripTags(
                    $this->priceHelper->currency($eachItem["price"])
                );
                $eachItem["formattedFinalPrice"] = $this->helper->getCurrencyConvertedFormattedPrice(
                    $item->getPriceInclTax()
                );
            }

            $todate = $product->getSpecialToDate();
            $fromdate = $product->getSpecialFromDate();

            $eachItem["isInRange"] = $this->helperCatalog->getIsInRange($todate, $fromdate);

            $eachItem["groupedProductId"] = 0;
            $eachItem["productId"] = $item->getProductId();
            if ($item->getProductType() == "grouped") {
                if (!empty($options["super_product_config"]) && !empty($options["super_product_config"]["product_id"])
                ) {
                    $eachItem["groupedProductId"] = $options["super_product_config"]["product_id"];
                }
            }
            $baseMessages = $item->getMessage(false);
            if ($baseMessages) {
                foreach ($baseMessages as $message) {
                    $messages = ["text"=>$message, "type"=>$item->getHasError() ? "error" : "notice"];
                    $eachItem["messages"][] = $messages;
                }
            } else {
                $eachItem["messages"] = [];
            }
            $this->items[] = $eachItem;
        }
        $cartItemData["items"] = $this->items;
        return $cartItemData;
    }

    /**
     * Function to get cross Sell Product List
     *
     * @return void
     */
    protected function getCrossSellList()
    {
        $crossSellList = [];
        if ($this->cartProductIds) {
            $filterProductIds = array_merge(
                $this->cartProductIds,
                $this->relatedProducts->getRelatedProductIds($this->quote->getAllItems())
            );
            $collection = $this->catalogLink
                ->create()
                ->useCrossSellLinks()
                ->getProductCollection()
                ->setStoreId($this->storeId)
                ->addStoreFilter()
                ->setPageSize(4)
                ->setVisibility($this->productVisibility->getVisibleInCatalogIds())
                ->addProductFilter($filterProductIds)
                ->addExcludeProductFilter($this->cartProductIds)
                ->setPageSize(4 - count($this->items))
                ->setGroupBy()
                ->setPositionOrder();
            foreach ($collection as $eachProduct) {
                $eachProduct = $this->productFactory->create()->load($eachProduct->getId());
                if ($eachProduct->isAvailable()) {
                    $crossSellList[] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $this->storeId,
                        $this->width,
                        $this->customerId
                    );
                }
            }
        }
        return $crossSellList;
    }

    /**
     * Function to get information about Total
     *
     * @return array
     */
    protected function getTotalsData()
    {
        $totals = [];
        $totalsData = [];
        if ($this->quote->isVirtual()) {
            $totals = $this->quote->getBillingAddress()->getTotals();
        } else {
            $totals = $this->quote->getShippingAddress()->getTotals();
        }
        $subtotal = [];
        $discount = [];
        $grandtotal = [];
        $shipping = [];
        if (isset($totals["subtotal"])) {
            $subtotal = $totals["subtotal"];
            $totalsData["subtotal"]["title"] = $subtotal->getTitle();
            $totalsData["subtotal"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice($subtotal->getValue());
            $totalsData["subtotal"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $subtotal->getValue()
            );
            $totalsData["subtotal"]["unformattedValue"] = $subtotal->getValue();
        }
        if (isset($totals["discount"])) {
            $discount = $totals["discount"];
            $totalsData["discount"]["title"] = $discount->getTitle();
            $totalsData["discount"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice($discount->getValue());
            $totalsData["discount"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $discount->getValue()
            );
            $totalsData["discount"]["unformattedValue"] = $discount->getValue();
        }
        if (isset($totals["shipping"])) {
            $shipping = $totals["shipping"];
            $totalsData["shipping"]["title"] = $shipping->getTitle();
            $totalsData["shipping"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice($shipping->getValue());
            $totalsData["shipping"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $shipping->getValue()
            );
            $totalsData["shipping"]["unformattedValue"] = $shipping->getValue();
        }
        if (isset($totals["tax"])) {
            $tax = $totals["tax"];
            $totalsData["tax"]["title"] = $tax->getTitle();
            $totalsData["tax"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice($tax->getValue());
            $totalsData["tax"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice($tax->getValue());
            $totalsData["tax"]["unformattedValue"] = $tax->getValue();
        }
        if (isset($totals["grand_total"])) {
            $grandtotal = $totals["grand_total"];
            $totalsData["grandtotal"]["title"] = __("Order Total");
            $totalsData["grandtotal"]["value"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $grandtotal->getValue()
            );
            $totalsData["grandtotal"]["formattedValue"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $grandtotal->getValue()
            );
            $this->returnArray["cartTotal"] = $this->helper->getCurrencyConvertedFormattedPrice(
                $grandtotal->getValue()
            );
            $this->returnArray["unformattedCartTotal"] = (float)$grandtotal->getValue();
            $totalsData["grandtotal"]["unformattedValue"] = $grandtotal->getValue();
        }
        return $totalsData;
    }
}
