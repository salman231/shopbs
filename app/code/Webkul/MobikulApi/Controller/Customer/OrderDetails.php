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
 * OrederDetails Class In Customer Controller
 */
class OrderDetails extends AbstractCustomer
{
    /**
     * Execute function for OrderDetails Class
     *
     * @return array message success
     */
    public function execute()
    {
        $this->returnArray["shippingMethod"] = __("No shipping information available");
        try {
            $this->verifyRequest();
            $cacheString = "ORDERDETAILS".$this->storeId.$this->incrementId;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->loadedOrder = $this->order->loadByIncrementId($this->incrementId);
            if (((int)$this->customerId > 0 && $this->loadedOrder->getCustomerId() != $this->customerId) ||
                !$this->loadedOrder || !$this->loadedOrder->getId()
            ) {
                $this->returnArray["message"] = __("Invalid Order.");
                return $this->getJsonResponse($this->returnArray);
            }
            $this->returnArray["hasShipments"] = (bool)$this->loadedOrder->hasShipments();
            $this->returnArray["hasInvoices"] = (bool)$this->loadedOrder->hasInvoices();
            if (count($this->loadedOrder->getCreditmemosCollection()) > 0) {
                $this->returnArray["hasCreditmemo"] = true;
            }
            $customer = $this->customerFactory->create()->load($this->customerId);
            $this->returnArray["customerName"] = $customer->getPrefix()." ".$customer->getFirstname()." ".
                $customer->getLastname();
            $this->returnArray["customerEmail"] = $customer->getEmail();
            $this->returnArray["state"] = $this->loadedOrder->getState();
            $this->returnArray["orderDate"] = $this->orderInfoBlock->formatDate(
                $this->loadedOrder->getCreatedAt(),
                \IntlDateFormatter::LONG
            );
            $this->returnArray["incrementId"] = $this->loadedOrder->getRealOrderId();
            $this->returnArray["statusLabel"] = $this->loadedOrder->getStatusLabel();
            $this->returnArray["statusColorCode"] = $this->helper->getOrderStatusColorCode(
                $this->loadedOrder->getStatus()
            );
            //Can reorder check
            $order = $this->orderRepository->get($this->loadedOrder->getEntityId());
            if (!$this->helper->getConfigData("sales/reorder/allow")) {
                $this->returnArray["canReorder"] = false;
            } elseif ($order->canReorder()) {
                $this->returnArray["canReorder"] = true;
            } else {
                $this->returnArray["canReorder"] = false;
            }
            $this->itemBlock = $this->orderItemRenderer;
            $this->priceBlock = $this->priceRenderer;
            // Getting Order Item and Order Totals Details //////////////////////////
            $this->orderDetailsnTotals();
            // Getting Order Invoice and Invoice Totals Details /////////////////////
            $this->getInvoicenTotals();
            // Getting Order Shipment and Sipment Totals Details ////////////////////
            $this->getShipmentnTotals();
            // Getting Order CreditMemo and CreditMemo Totals Details ///////////////
            $this->getCreditmemonTotals();
            // Collecting order information /////////////////////////////////////////
            $infoBlock = $this->orderInfoBlock;
            if ($this->loadedOrder->getShippingAddress()) {
                $this->returnArray["shippingAddress"] = $infoBlock->getFormattedAddress(
                    $this->loadedOrder->getShippingAddress()
                );
            }
            if ($this->loadedOrder->getShippingDescription()) {
                $this->returnArray["shippingMethod"] = $infoBlock->escapeHtml(
                    $this->loadedOrder->getShippingDescription()
                );
            }
            $this->returnArray["billingAddress"] = $infoBlock->getFormattedAddress(
                $this->loadedOrder->getBillingAddress()
            );
            $this->returnArray["paymentMethod"] = $this->loadedOrder->getPayment()->getMethodInstance()->getTitle();
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            if (hash("sha256", $encodedData) == $this->eTag) {
                $this->returnArray["statusCode"] = 304;
                $cacheStatus = (bool)$this->helper->getConfigData("mobikul/cachesettings/enable");
                if ($cacheStatus) {
                    $counter = $this->helper->getConfigData("mobikul/cachesettings/counter");
                    if ($counter == "") {
                        $counter = 1;
                    }
                    return $this->getJsonResponse($this->returnArray, 304);
                }
            }
            $this->helper->updateCache($cacheString, $encodedData);
            $this->returnArray["eTag"] = hash("sha256", $encodedData);
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
            $this->incrementId = $this->wholeData["incrementId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId  = $this->helper->getCustomerByToken($this->customerToken);
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

    /**
     * Function orderDetailsnTotals
     * Set order data in return array
     *
     * @return null
     */
    protected function orderDetailsnTotals()
    {
        $items = $this->loadedOrder->getItemsCollection();
        $itemList = [];
        $orderData = [];
        foreach ($items as $item) {
            $this->itemBlock->setItem($item);
            $this->priceBlock->setItem($item);
            if ($item->getParentItem()) {
                continue;
            }
            $eachItem = [];
            $eachItem["name"] = htmlspecialchars_decode($item->getName());
            $eachItem["productId"] = $item->getProductId();
            if ($options = $this->itemBlock->getItemOptions()) {
                foreach ($options as $option) {
                    $eachOption = [];
                    $eachOption["label"] = htmlspecialchars_decode($option["label"]);
                    if (is_array($option["value"])) {
                        $eachOption["value"] = $option["value"];
                    } else {
                        $eachOption["value"][] = $this->helperCatalog->stripTags($option["value"]);
                    }
                    $eachItem["option"][] = $eachOption;
                }
            } else {
                $eachItem["option"] = [];
            }
            $eachItem["sku"] = $this->itemBlock->prepareSku($this->itemBlock->getSku());
            if ($this->priceBlock->displayPriceInclTax() || $this->priceBlock->displayBothPrices()) {
                $eachItem["price"] = $this->loadedOrder->formatPriceTxt($this->itemBlock->getUnitDisplayPriceInclTax());
            }
            if ($this->priceBlock->displayPriceExclTax() || $this->priceBlock->displayBothPrices()) {
                $eachItem["price"] = $this->loadedOrder->formatPriceTxt(
                    $this->priceBlock->getUnitDisplayPriceExclTax()
                );
            }
            $eachItem["qty"]["Ordered"] = $this->itemBlock->getItem()->getQtyOrdered()*1;
            $eachItem["qty"]["Shipped"] = $this->itemBlock->getItem()->getQtyShipped()*1;
            $eachItem["qty"]["Canceled"] = $this->itemBlock->getItem()->getQtyCanceled()*1;
            $eachItem["qty"]["Refunded"] = $this->itemBlock->getItem()->getQtyRefunded()*1;
            if (($this->priceBlock->displayPriceInclTax() || $this->priceBlock->displayBothPrices()) &&
                !$item->getNoSubtotal()
            ) {
                $eachItem["subTotal"] = $this->loadedOrder->formatPriceTxt(
                    $this->priceBlock->getRowDisplayPriceInclTax()
                );
            }
            if ($this->priceBlock->displayPriceExclTax() || $this->priceBlock->displayBothPrices()) {
                $eachItem["subTotal"] = $this->loadedOrder->formatPriceTxt(
                    $this->priceBlock->getRowDisplayPriceExclTax()
                );
            }
            $itemList[] = $eachItem;
        }
        $itemList = $this->addProductImages($itemList);
        $orderData["itemList"] = $itemList;
        $totals = [];
        $totalsBlock = $this->orderTotals;
        $totalsBlock->setOrder($this->loadedOrder);
        $totalsBlock->_initTotals();
        foreach ($totalsBlock->getTotals() as $total) {
            $eachTotal = [];
            $eachTotal["code"] = $total->getCode();
            $eachTotal["label"] = $total->getLabel();
            $eachTotal["value"] = $this->helperCatalog->stripTags($total->getValue());
            $eachTotal["formattedValue"] = $this->helperCatalog->stripTags($totalsBlock->formatValue($total));
            $totals[] = $eachTotal;
            if ($total->getCode() == "grand_total") {
                $this->returnArray["orderTotal"] = $eachTotal["formattedValue"];
            }
        }
        $eachTotal = [];
        $eachTotal["code"] = "tax";
        $eachTotal["label"] = __("Tax");
        $eachTotal["value"] = $this->loadedOrder->getTaxAmount();
        $eachTotal["formattedValue"] = $this->loadedOrder->formatPriceTxt($this->loadedOrder->getTaxAmount());
        $totals[] = $eachTotal;
        $orderData["totals"] = $totals;
        $this->returnArray["orderData"] = $orderData;
    }

    /**
     * Function to add Invoice List in return array
     *
     * @return null
     */
    protected function getInvoicenTotals()
    {
        $invoiceList = [];
        foreach ($this->loadedOrder->getInvoiceCollection() as $invoice) {
            $eachInvoice = [];
            $eachInvoice['id'] = $invoice->getId();
            $eachInvoice["incrementId"] = $invoice->getIncrementId();
            $items = $invoice->getAllItems();
            foreach ($items as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                $this->itemBlock->setItem($item);
                $this->priceBlock->setItem($item);
                $eachItemData = [];
                $eachItemData["name"] = $this->itemBlock->escapeHtml($item->getName());
                if ($options = $this->itemBlock->getItemOptions()) {
                    $eachItemData = $this->getItemData($options);
                } else {
                    $eachItemData["option"] = [];
                }
                $eachInvoice["items"][] = $eachItemData;
                $eachInvoice["sku"] = $this->itemBlock->prepareSku($this->itemBlock->getSku());
                if ($this->priceBlock->displayPriceInclTax() || $this->priceBlock->displayBothPrices()) {
                    $eachInvoice["price"] = $this->loadedOrder->formatPriceTxt(
                        $this->priceBlock->getUnitDisplayPriceInclTax()
                    );
                }
                if ($this->priceBlock->displayPriceExclTax() || $this->priceBlock->displayBothPrices()) {
                    $eachInvoice["price"] = $this->loadedOrder->formatPriceTxt(
                        $this->priceBlock->getUnitDisplayPriceExclTax()
                    );
                }
                $eachInvoice["qty"] = $item->getQty()*1;
                if (($this->priceBlock->displayPriceInclTax() || $this->priceBlock->displayBothPrices()) &&
                    !$item->getNoSubtotal()
                ) {
                    $eachInvoice["subTotal"] = $this->loadedOrder->formatPriceTxt(
                        $this->priceBlock->getRowDisplayPriceInclTax()
                    );
                }
                if ($this->priceBlock->displayPriceExclTax() || $this->priceBlock->displayBothPrices()) {
                    $eachInvoice["subTotal"] = $this->loadedOrder->formatPriceTxt(
                        $this->priceBlock->getRowDisplayPriceExclTax()
                    );
                }
            }
            $invoiceTotalsBlock = $this->invoiceTotals;
            $invoiceTotalsBlock->setInvoice($invoice);
            $invoiceTotalsBlock->setOrder($this->loadedOrder);
            $invoiceTotalsBlock->_initTotals();
            $totals = [];
            foreach ($invoiceTotalsBlock->getTotals() as $total) {
                $eachTotal = [];
                $eachTotal["code"] = $total->getCode();
                $eachTotal["label"] = $total->getLabel();
                $eachTotal["value"] = $total->getValue();
                $eachTotal["formattedValue"] = $this->helperCatalog->stripTags(
                    $invoiceTotalsBlock->formatValue($total)
                );
                $totals[] = $eachTotal;
            }
            $eachTotal = [];
            $eachTotal["code"] = "tax";
            $eachTotal["label"] = __("Tax");
            $eachTotal["value"] = $this->loadedOrder->getTaxAmount();
            $eachTotal["formattedValue"] = $this->loadedOrder->formatPriceTxt($this->loadedOrder->getTaxAmount());
            $totals[] = $eachTotal;
            $eachInvoice["totals"] = $totals;
            $invoiceList[] = $eachInvoice;
        }
        $this->returnArray["invoiceList"] = $invoiceList;
    }

    protected function getItemData($options)
    {
        $eachItemData = [];
        foreach ($options as $option) {
            $value = null;
            $eachOption = [];
            $eachOption["label"] = $option["label"];
            if (!$this->itemBlock->getPrintStatus()) {
                $formatedOptionValue = $this->itemBlock->getFormatedOptionValue($option);
                if (isset($formatedOptionValue["full_view"])) {
                    $value = $formatedOptionValue["full_view"];
                } else {
                    $value = $formatedOptionValue["value"];
                }
            } else {
                $value = $option["print_value"] ?? $option["value"];
            }
            if (!is_array($value)) {
                $eachOption["value"][] = $value;
            } else {
                $eachOption["value"] = $value;
            }
            $eachItemData["option"][] = $eachOption;
        }
        return $eachItemData;
    }

    protected function addProductImages($items)
    {
        $productIds = [];
        foreach ($items as $key => $item) {
            $productIds[$key] = $item["productId"];
        }
        $collection = $this->productFactory->create()->getCollection()
            ->AddAttributeToSelect("image")
            ->addFieldToFilter("entity_id", ["in"=>$productIds]);
        foreach ($collection as $coll) {
            foreach ($items as $key => $item) {
                if ($item["productId"] == $coll->getEntityId()) {
                    $items[$key]["image"] = $this->helper->getUrl("media")."catalog/product".$coll->getImage();
                    continue;
                }
            }
        }
        return $items;
    }

    /**
     * Function getShipmentnTotals
     * add ShipmentnTotals to return array
     *
     * @return null
     */
    protected function getShipmentnTotals()
    {
        $shipmentList = [];
        foreach ($this->loadedOrder->getShipmentsCollection() as $shipment) {
            $eachShipment = [];
            $eachShipment["incrementId"] = $shipment->getIncrementId();
            $items = $shipment->getAllItems();
            foreach ($items as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                $eachshipmentItem = [];
                $this->itemBlock->setItem($item);
                $eachshipmentItem["name"] = $this->itemBlock->escapeHtml($item->getName());
                if ($options = $this->itemBlock->getItemOptions()) {
                    foreach ($options as $option) {
                        $eachshipmentItem = $this->getShipmentItemData($option);
                    }
                } else {
                    $eachshipmentItem["option"] = [];
                }
                $eachshipmentItem["sku"] = $this->itemBlock->prepareSku($this->itemBlock->getSku());
                $eachshipmentItem["qty"] = $item->getQty()*1;
                $eachShipment["items"][] = $eachshipmentItem;
            }
            $shipmentList[] = $eachShipment;
        }
        $this->returnArray["shipmentList"] = $shipmentList;
    }

    protected function getShipmentItemData($option)
    {
        $value = "";
        $eachOption = [];
        $eachOption["label"] = $option["label"];
        if (!$this->itemBlock->getPrintStatus()) {
            $formatedOptionValue = $this->itemBlock->getFormatedOptionValue($option);
            if (isset($formatedOptionValue["full_view"])) {
                $value = $formatedOptionValue["full_view"];
            } else {
                $value = $formatedOptionValue["value"];
            }
        } else {
            $value = isset($option["print_value"]) ? $option["print_value"] : $option["value"];
        }
        if (!is_array($value)) {
            $eachOption["value"][] = $value;
        } else {
            $eachOption["value"] = $value;
        }
        $eachshipmentItem["option"][] = $eachOption;
        return $eachshipmentItem;
    }

    /**
     * Function getCreditmemonTotals
     * add CreditmemonTotals to return array
     *
     * @return null
     */
    protected function getCreditmemonTotals()
    {
        $creditmemoList = [];
        foreach ($this->loadedOrder->getCreditmemosCollection() as $creditmemo) {
            $eachCreditmemo = [];
            $eachCreditmemo["incrementId"] = $creditmemo->getIncrementId();
            $items = $creditmemo->getAllItems();
            foreach ($items as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                $eachcreditmemoItem = [];
                $this->itemBlock->setItem($item);
                $eachcreditmemoItem = $this->getCreditMemoItemData($item);
                $eachCreditmemo["items"][] = $eachcreditmemoItem;
                $creditMemoTotalsBlock = $this->creditmemoTotals;
                $creditMemoTotalsBlock->setCreditmemo($creditmemo);
                $creditMemoTotalsBlock->setOrder($this->loadedOrder);
                $creditMemoTotalsBlock->_initTotals();
                $totals = [];
                foreach ($creditMemoTotalsBlock->getTotals() as $total) {
                    $eachTotal = [];
                    $eachTotal["code"] = $total->getCode();
                    $eachTotal["label"] = $total->getLabel();
                    $eachTotal["value"] = $total->getValue();
                    $eachTotal["formattedValue"] = $this->helperCatalog->stripTags(
                        $this->invoiceTotals->formatValue($total)
                    );
                    $totals[] = $eachTotal;
                }
                $eachTotal = [];
                $eachTotal["code"] = "tax";
                $eachTotal["label"] = __("Tax");
                $eachTotal["value"] = $this->loadedOrder->getTaxAmount();
                $eachTotal["formattedValue"] = $this->loadedOrder->formatPriceTxt($this->loadedOrder->getTaxAmount());
                $totals[] = $eachTotal;
                $eachCreditmemo["totals"] = $totals;
            }
            $creditmemoList[] = $eachCreditmemo;
        }
        $this->returnArray["creditmemoList"] = $creditmemoList;
    }

    protected function getCreditMemoItemData($item)
    {
        $eachcreditmemoItem["name"] = $this->itemBlock->escapeHtml($item->getName());
        if ($options = $this->itemBlock->getItemOptions()) {
            foreach ($options as $option) {
                $eachOption = [];
                $eachOption["label"] = $option["label"];
                if (!$this->itemBlock->getPrintStatus()) {
                    $formatedOptionValue = $this->itemBlock->getFormatedOptionValue($option);
                    if (isset($formatedOptionValue["full_view"])) {
                        $eachOption["value"] = $formatedOptionValue["full_view"];
                    } else {
                        $eachOption["value"] = $formatedOptionValue["value"];
                    }
                } else {
                    $eachOption["value"] = $option["print_value"] ?? $option["value"];
                }
                $eachcreditmemoItem["option"][] = $eachOption;
            }
        } elseif ($links = $this->itemBlock->getLinks()) {
            $eachOption = [];
            $eachOption["label"] = $this->itemBlock->getLinksTitle();
            foreach ($links->getPurchasedItems() as $link) {
                $eachOption["value"] = $this->itemBlock->escapeHtml($link->getLinkTitle());
            }
            $eachcreditmemoItem["option"][] = $eachOption;
        } else {
            $eachcreditmemoItem["option"] = [];
        }
        $eachcreditmemoItem["sku"] = $this->itemBlock->prepareSku($this->itemBlock->getSku());
        $this->priceBlock->setItem($item);
        if ($this->priceBlock->displayPriceInclTax() || $this->priceBlock->displayBothPrices()) {
            $eachcreditmemoItem["price"] = $this->loadedOrder->formatPriceTxt(
                $block->getUnitDisplayPriceInclTax()
            );
        }
        if ($this->priceBlock->displayPriceExclTax() || $this->priceBlock->displayBothPrices()) {
            $eachcreditmemoItem["price"] = $this->loadedOrder->formatPriceTxt(
                $this->priceBlock->getUnitDisplayPriceExclTax()
            );
        }
        $eachcreditmemoItem["qty"] = $item->getQty()*1;
        if (($this->priceBlock->displayPriceInclTax() || $this->priceBlock->displayBothPrices()) &&
            !$item->getNoSubtotal()
        ) {
            $eachcreditmemoItem["subTotal"] = $this->loadedOrder->formatPriceTxt(
                $this->priceBlock->getRowDisplayPriceInclTax()
            );
        }
        if ($this->priceBlock->displayPriceExclTax() || $this->priceBlock->displayBothPrices()) {
            $eachcreditmemoItem["subTotal"] = $this->loadedOrder->formatPriceTxt(
                $this->priceBlock->getRowDisplayPriceExclTax()
            );
        }
        $eachcreditmemoItem["discountAmount"] = $this->loadedOrder->formatPriceTxt(-$item->getDiscountAmount());
        $eachcreditmemoItem["rowTotal"] = $this->loadedOrder->formatPriceTxt(
            $this->itemBlock->getTotalAmount($item)
        );

        return $eachcreditmemoItem;
    }
}
