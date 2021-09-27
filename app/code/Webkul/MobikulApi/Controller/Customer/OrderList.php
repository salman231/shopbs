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

class OrderList extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "ORDERLIST".$this->storeId.$this->customerToken.$this->pageNumber;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $orderCollection = $this->orderCollection
                ->addFieldToSelect("*")
                ->addFieldToFilter("customer_id", $this->customerId)
                ->addFieldToFilter("status", ["in"=>$this->orderConfig->getVisibleOnFrontStatuses()])
                ->setOrder("created_at", "DESC");
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $orderCollection->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $orderCollection->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            // apply pagination for dashboard page //////////////////////////////////
            if ($this->forDashboard) {
                $orderCollection->setPageSize(5)->setCurPage(1);
            }
            // adding required Count ////////////////////////////////////////////////
            if ($this->requiredCount > 0) {
                $orderCollection->setPageSize($this->requiredCount)->setCurPage(1);
            }
            // Creating Order List //////////////////////////////////////////////////
            $orderList = [];
            foreach ($orderCollection as $key => $order) {
                $eachOrder = [];
                $eachOrder["id"] = $key;
                $eachOrder["date"] = $this->orderHistoryBlock->formatDate($order->getCreatedAt());
                $eachOrder["state"] = $order->getState();
                $eachOrder["status"] = $order->getStatusLabel();
                $eachOrder["ship_to"] = $order->getShippingAddress() ? $this->helperCatalog->stripTags(
                    $order->getShippingAddress()->getName()
                ) : " ";
                $eachOrder["order_id"] = $order->getRealOrderId();
                $eachOrder["item_count"] = $order->getTotalItemCount();
                $eachOrder["order_total"] = $this->helperCatalog->stripTags(
                    $order->formatPrice($order->getGrandTotal())
                );
                $eachOrder["item_image_url"] = $this->getItemImageUrl($order);
                $eachOrder["statusColorCode"] = $this->helper->getOrderStatusColorCode($order->getStatus());
                $canReorder = false;
                if ($this->canReorder($order)) {
                    $canReorder = $this->canReorder($order);
                }
                $eachOrder["canReorder"] = $canReorder;
                $orderList[] = $eachOrder;
            }
            $this->returnArray["success"] = true;
            $this->returnArray["orderList"] = $orderList;
            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            if (hash("sha256", $encodedData) == $this->eTag) {
                $this->returnArray["statusCode"] = 304;
                $cacheStatus = (bool)$this->helper->getConfigData("mobikul/cachesettings/enable");
                if ($cacheStatus) {
                    $counter = $this->helper->getConfigData("mobikul/cachesettings/counter");
                    if ($counter == "") {
                        $counter = 5;
                    }
                    return $this->getJsonResponse($this->returnArray, 304);
                }
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
     * Verify Request function to verify the request
     *
     * @return void|jSon
     */
    protected function getItemImageUrl($order)
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $flagImage = 0;
        $image = "";
        foreach ($order->getAllItems() as $item) {
            $productId = $item->getProductId();
            if ($flagImage) {
                continue;
            } else {
                $image = $this->productFactory->create()->load($productId)->getImage();
                $flagImage = 1;
            }
        }
        if ($image) {
            $imageUrl = $mediaUrl."catalog/product/".$image;
            return $imageUrl;
        } else {
            return "";
        }
    }

    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->requiredCount = $this->wholeData["requiredCount"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
