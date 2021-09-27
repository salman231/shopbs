<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulAdmin
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Customer;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Webkul\MobikulApi\Controller\Customer\AbstractCustomer;

/**
 * Class InvoiceView
 * To get All the details of Invoice by Id
 */
class InvoiceView extends AbstractCustomer
{
    /**
     * Current Order Object with which the invoice belongs to
     *
     * @var loadedOrder
     */
    protected $loadedOrder;

    /**
     * Execute function for Class Invoice Details
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "INVOICEVIEW".$this->storeId.$this->customerToken.$this->eTag;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->invoice = $this->invoiceRepository->get($this->invoiceId);
            $this->loadedOrder = $this->invoice->getOrder();
            $this->coreRegistry->register('current_order', $this->loadedOrder);
            $this->coreRegistry->register('current_invoice', $this->invoice);
            $this->returnArray["orderId"] = (int)$this->loadedOrder->getId();
            // Get Invoice Item Details /////////////////////////////////////////////
            $this->itemBlock = $this->orderItemRenderer;
            $this->priceBlock = $this->priceRenderer;
            $invoiceItems = $this->invoice->getItemsCollection();
            $items = $this->invoiceItemCollectionFactory->create()->setInvoiceFilter($this->invoiceId);
            $this->getInvoiceItemsData($items);
            $this->getInvoiceTotalsInformation();

            $shippingAddress = $this->loadedOrder->getShippingAddress();
            $this->returnArray['shippingAddress']['firstname'] = $shippingAddress->getFirstname();
            $this->returnArray['shippingAddress']['lastname'] = $shippingAddress->getLastname();
            $this->returnArray['shippingAddress']['city'] = $shippingAddress->getCity();
            $this->returnArray['shippingAddress']['region'] = $shippingAddress->getRegion();
            $this->returnArray['shippingAddress']['street'] = $shippingAddress->getStreet();
            $this->returnArray['shippingAddress']['country'] = $this->country->loadByCode(
                $shippingAddress->getCountryId()
            )->getName();
            $this->returnArray['shippingAddress']['pincode'] = $shippingAddress->getPostCode();

            $billingAddress = $this->loadedOrder->getBillingAddress();
            $this->returnArray['billingAddress']['firstname'] = $billingAddress->getFirstname();
            $this->returnArray['billingAddress']['lastname'] = $billingAddress->getLastname();
            $this->returnArray['billingAddress']['city'] = $billingAddress->getCity();
            $this->returnArray['billingAddress']['region'] = $billingAddress->getRegion();
            $this->returnArray['billingAddress']['street'] = $billingAddress->getStreet();
            $this->returnArray['billingAddress']['country'] = $this->country->loadByCode(
                $billingAddress->getCountryId()
            )->getName();
            $this->returnArray['billingAddress']['pincode'] = $billingAddress->getPostCode();

            $this->returnArray['shippingMethod']['title'] = $this->loadedOrder->getShippingDescription();

            $payment = $this->loadedOrder->getPayment();
            $method = $payment->getMethodInstance();
            $this->returnArray['paymentMethod']['title'] = $method->getTitle();

            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            if (hash("sha256", $encodedData) == $this->eTag) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $this->helper->updateCache($cacheString, $encodedData);
            $this->returnArray["eTag"] = hash("sha256", $encodedData);
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->returnArray["success"] = true;
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function verifyRequest
     * verify and validate request
     *
     * @return json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->invoiceId = $this->wholeData["invoiceId"] ?? 0;
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
            throw new \BadMethodCallExceptionc(__("Invalid Request"));
        }
    }

    /**
     * Function to get Invoice Items data
     *
     * @param Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory $items items
     *
     * @return void
     */
    public function getInvoiceItemsData($items)
    {
        if (count($items) > 0) {
            foreach ($items as $item) {
                $this->itemBlock->setItem($item);
                $this->priceBlock->setItem($item);
                $eachItem = [];
                $eachItem["id"] = $item->getId();
                $eachItem["name"] = $item->getName();
                $eachItem["productId"] = $item->getProductId();
                $eachItem["sku"] = $this->itemBlock->prepareSku($this->itemBlock->getSku());
                if ($options = $this->itemBlock->getItemOptions()) {
                    $eachItem["option"] = $this->getItemData($options);
                } else {
                    $eachItem["option"] = [];
                }
                $eachItem["qty"] = $item->getQty()*1;
                $eachItem["taxAmount"] = strip_tags($this->invoiceItemRenderer->displayPriceAttribute("tax_amount"));
                $eachItem["discountAmount"] = strip_tags(
                    $this->invoiceItemRenderer->displayPriceAttribute("discount_amount")
                );
                if ($this->adminPriceRenderer->displayBothPrices() || $this->adminPriceRenderer->displayPriceExclTax()
                ) {
                    $eachItem["price"] = strip_tags($this->adminPriceRenderer->getUnitPriceExclTaxHtml());
                    $eachItem["subTotal"] = strip_tags($this->adminPriceRenderer->getRowPriceExclTaxHtml());
                }
                if ($this->adminPriceRenderer->displayBothPrices() || $this->adminPriceRenderer->displayPriceInclTax()
                ) {
                    $eachItem["price"] = strip_tags($this->adminPriceRenderer->getUnitPriceInclTaxHtml());
                    $eachItem["subTotal"] = strip_tags($this->adminPriceRenderer->getRowPriceInclTaxHtml());
                }
                $eachItem["rowTotal"] = strip_tags(
                    $this->adminPriceRenderer->displayPrices(
                        $this->adminPriceRenderer->getBaseTotalAmount($item),
                        $this->adminPriceRenderer->getTotalAmount($item)
                    )
                );
                $this->returnArray["itemList"][] = $eachItem;
            }
        }
    }

    public function getItemData($options)
    {
        $eachItem = [];
        foreach ($options as $option) {
            $value = null;
            $eachOption = [];
            $eachOption["label"] = $this->itemBlock->escapeHtml($option["label"]);
            if (!$this->itemBlock->getPrintStatus()) {
                $formatedOptionValue = $this->itemBlock->getFormatedOptionValue($option);
                if (isset($formatedOptionValue["full_view"])) {
                    $value = $formatedOptionValue["full_view"];
                } else {
                    $value = $formatedOptionValue["value"];
                }
            } else {
                $value = nl2br($this->itemBlock->escapeHtml(
                    (isset($option["print_value"]) ? $option["print_value"] : $option["value"])
                ));
            }
            if (!is_array($value)) {
                $eachOption["value"][] = $value;
            } else {
                $eachOption["value"] = $value;
            }
            $eachItem[] = $eachOption;
        }
        return $eachItem;
    }

    /**
     * Function to get totals information
     *
     * @return void
     */
    public function getInvoiceTotalsInformation()
    {
        $this->invoiceTotals->_initTotals();
        $footerTotals = $this->invoiceTotals->getTotals('footer');
        if ($footerTotals) {
            foreach ($footerTotals as $total) {
                $eachItem["code"] = $this->invoiceTotals->escapeHtml($total->getCode());
                $eachItem["label"] = $this->invoiceTotals->escapeHtml($total->getLabel());
                $eachItem["value"] = $total->getValue();
                $eachItem["formattedValue"] = strip_tags($this->invoiceTotals->formatValue($total));
                $this->returnArray["totals"][] = $eachItem;
            }
        }
        $invoiceTotals = $this->invoiceTotals->getTotals("");
        if ($invoiceTotals) {
            foreach ($invoiceTotals as $total) {
                $label = $total->getLabel();
                $eachItem["code"] = $this->invoiceTotals->escapeHtml($total->getCode());
                $eachItem["label"] = $this->invoiceTotals->escapeHtml($label);
                $eachItem["value"] = $total->getValue();
                $eachItem["formattedValue"] = strip_tags($this->invoiceTotals->formatValue($total));
                $this->returnArray["totals"][] = $eachItem;
                if ($label == "Grand Total") {
                    $this->returnArray["cartTotal"] = strip_tags($this->invoiceTotals->formatValue($total));
                }
            }
        }
    }
}
