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

class MyDownloadsList extends AbstractCustomer
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "MYDOWNLOADLIST".$this->storeId.$this->pageNumber.$this->customerToken;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("As customer you are requesting does not exist, so you need to logout.")
                );
            }
            $purchased = $this->purchasedLinkCollection->addFieldToFilter(
                "customer_id",
                $this->customerId
            )->addOrder("created_at", "DESC");
            $purchasedIds = [];
            foreach ($purchased as $item) {
                $purchasedIds[] = $item->getId();
            }
            if (empty($purchasedIds)) {
                $purchasedIds = [null];
            }
            $purchasedItems = $this->purchasedLinkItemCollection
                ->addFieldToFilter("purchased_id", ["in"=>$purchasedIds])
                ->addFieldToFilter("status", [
                    "nin"=>[
                        \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING_PAYMENT,
                        \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW
                    ]
                ])
                ->setOrder("item_id", "desc");
            // Applying pagination //////////////////////////////////////////////////
            if ($this->pageNumber >= 1) {
                $this->returnArray["totalCount"] = $purchasedItems->getSize();
                $pageSize = $this->helperCatalog->getPageSize();
                $purchasedItems->setPageSize($pageSize)->setCurPage($this->pageNumber);
            }
            foreach ($purchasedItems as $item) {
                $item->setPurchased($purchased->getItemById($item->getPurchasedId()));
            }
            // Creating Downloads List //////////////////////////////////////////////
            $downloadsList = [];
            $block = $this->listProduct;
            foreach ($purchasedItems as $downloads) {
                $eachDownloads = [];
                $eachDownloads["incrementId"] = $incrementId = $downloads->getPurchased()->getOrderIncrementId();
                $order = $this->order->loadByIncrementId($incrementId);
                if ($order->getRealOrderId() > 0) {
                    $eachDownloads["isOrderExist"] = true;
                    $eachDownloads["message"] = "";
                } else {
                    $eachDownloads["isOrderExist"] = false;
                    $eachDownloads["message"] = __("Sorry This Order Does not Exist!!");
                }
                $eachDownloads["hash"] = $downloads->getLinkHash();
                $eachDownloads["date"] = $block->formatDate($downloads->getPurchased()->getCreatedAt());
                $eachDownloads["state"] = $downloads->getStatus() == 'expired'? 'downloaded':$order->getState();
                $eachDownloads["status"] = __(ucfirst($downloads->getStatus()));
                $eachDownloads["statusColorCode"] = $this->helper->getOrderStatusColorCode($downloads->getStatus());
                $eachDownloads["proName"] = $this->helperCatalog->stripTags($downloads->getPurchased()
                    ->getProductName());
                $eachDownloads["remainingDownloads"] = $block->getRemainingDownloads($downloads);
                $canReorder = false;
                if ($this->canReorder($order)) {
                    $canReorder = $this->canReorder($order);
                }
                $eachDownloads["canReorder"] = $canReorder;
                $downloadsList[] = $eachDownloads;
            }
            $this->returnArray["downloadsList"] = $downloadsList;
            $this->returnArray["success"] = true;
            $this->emulate->stopEnvironmentEmulation($environment);
            $encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
            if (hash("sha256", $encodedData) == $this->eTag) {
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
            $this->pageNumber = $this->wholeData["pageNumber"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
