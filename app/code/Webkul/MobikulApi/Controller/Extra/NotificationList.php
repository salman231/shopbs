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

namespace Webkul\MobikulApi\Controller\Extra;

class NotificationList extends AbstractMobikul
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $cacheString = "NOTIFICATIONLIST".$this->width.$this->storeId.$this->mFactor;
            if ($this->helper->validateRequestForCache($cacheString, $this->eTag)) {
                return $this->getJsonResponse($this->returnArray, 304);
            }
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $notificationCollection = $this->mobikulNotification
                ->create()
                ->getCollection()
                ->addFieldToFilter("status", 1)
                ->addFieldToFilter([
                    'store_id',
                    'store_id'
                ], [
                    ["finset" => 0],
                    ["finset" => $this->storeId]
                ])
                ->setOrder("updated_at", "DESC");
            $height = $this->helper->getValidDimensions($this->mFactor, 2*($this->width/3));
            $this->width = $this->helper->getValidDimensions($this->mFactor, $this->width);
            foreach ($notificationCollection as $notification) {
                $eachNotification = [];
                $eachNotification["id"] = $notification->getId();
                $eachNotification["title"] = $notification->getTitle();
                $eachNotification["content"] = implode(
                    " ",
                    array_slice(explode(" ", $notification->getContent()), 0, 10)
                );
                $eachNotification["notificationType"] = $notification->getType();
                $basePath = $this->baseDir.DS."mobikul".DS."notification".DS.$notification->getFilename();
                if ($this->fileDriver->isFile($basePath)) {
                    $newPath = $this->baseDir.DS."mobikulresized".DS.$this->width."x".
                        $height.DS."notification".DS.$notification->getFilename();
                    $this->helperCatalog->resizeNCache($basePath, $newPath, $this->width, $height);
                    $eachNotification["banner"] = $this->helper->getUrl("media")."mobikulresized".DS.$this->width."x".
                        $height.DS."notification".DS.$notification->getFilename();
                    $dominantColorPath = $this->helper->getBaseMediaDirPath()."mobikulresized".DS.$this->width."x".
                        $height.DS."notification".DS.$notification->getFilename();
                } else {
                    $eachNotification["banner"] = "";
                    $dominantColorPath = "";
                }
                $eachNotification["dominantColor"] = $this->helper->getDominantColor($dominantColorPath);
                if ($notification->getType() == "category") {
                    // for category /////////////////////////////////////////////////
                    $category = $this->categoryFactory->create()->load($notification->getProCatId());
                    $eachNotification["categoryName"] = $category->getName();
                    $eachNotification["categoryId"] = $notification->getProCatId();
                } elseif ($notification->getType() == "product") {
                    // for product //////////////////////////////////////////////////
                    $product = $this->productFactory->create()->load($notification->getProCatId());
                    $eachNotification["productName"] = $product->getName();
                    $eachNotification["productType"] = $product->getTypeId();
                    $eachNotification["productId"] = $notification->getProCatId();
                }
                $this->returnArray["notificationList"][] = $eachNotification;
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
    
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->mFactor = $this->wholeData["mFactor"] ?? 1;
            $this->mFactor = $this->helper->calcMFactor($this->mFactor);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
