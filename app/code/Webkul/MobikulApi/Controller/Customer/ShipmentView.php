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

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Webkul\MobikulApi\Controller\Customer\AbstractCustomer;

/**
 * Class Shipment view
 * To get All the details of Shipment at Customer end
 */
class ShipmentView extends AbstractCustomer
{
    /**
     * @var loadedOrder
     */
    protected $loadedOrder;

    /**
     * Execute funciton for
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "SHIPMENTVIEW".$this->storeId.$this->customerToken.$this->eTag;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->shipment = $this->shipmentRepositoryInterface->get($this->shipmentId);
            $this->loadedOrder = $this->shipment->getOrder();
            $this->coreRegistry->register("current_order", $this->loadedOrder);
            $this->coreRegistry->register("current_shipment", $this->shipment);
            $this->returnArray["orderId"] = (int)$this->loadedOrder->getId();
            // Get Invoice Item Details /////////////////////////////////////////////
            $this->itemBlock = $this->orderItemRenderer;
            $this->priceBlock = $this->priceRenderer;
            $shipmentItems = $this->shipment->getAllItems();
            $this->getShipmentItemsData($shipmentItems);
            $this->getTrackingData();
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
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->shipmentId = $this->wholeData["shipmentId"] ?? 0;
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

    /**
     * Function to get Invoice Items data
     *
     * @param Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory $items items
     *
     * @return void
     */
    public function getShipmentItemsData($items)
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
                    foreach ($options as $option) {
                        $eachOption = $this->getOptions($option);
                        $eachItem["option"][] = $eachOption;
                    }
                } else {
                    $eachItem["option"] = [];
                }
                $eachItem["qty"] = $item->getQty()*1;
                $this->returnArray["itemList"][] = $eachItem;
            }
        }
    }

    /**
     * GetOptions function
     *
     * @param [Array] $option
     * @return Array
     */
    public function getOptions($option)
    {
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
                (isset($option["print_value"]) ? $option["print_value"] : $option["value"]
                )
            ));
        }
        if (!is_array($value)) {
            $eachOption["value"][] = $value;
        } else {
            $eachOption["value"] = $value;
        }
        return $eachOption;
    }

    /**
     * Function to add tracking data in return array
     *
     * @return void
     */
    public function getTrackingData()
    {
        $trackingData = $this->shipment->getAllTracks();
        foreach ($trackingData as $track) {
            $eachTrack = [
                "id" => $track->getId(),
                "number" => $track->getNumber(),
                "title" => $this->shippingView->escapeHtml($track->getTitle()),
                "carrier" => $this->shippingView->escapeHtml(
                    $this->shippingView->getCarrierTitle($track->getCarrierCode())
                )
            ];
            $this->returnArray["trackingData"][] = $eachTrack;
        }
    }
}
