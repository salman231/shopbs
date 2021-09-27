<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Notification;

/**
 * Push Class controller
 */
class Push extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $notificationId = $this->initCurrentNotification();
        if (!empty($notificationId)) {
            $notification = $this->notificationRepository->getById($notificationId);
            $baseTmpPath = "mobikul/notification/";
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).$baseTmpPath;
            $bannerUrl = "";
            if ($notification->getFilename() != "") {
                $bannerUrl = $baseUrl.$notification->getFilename();
            }
            $message = [
                "id" => $notification->getId(),
                "body" => $notification->getContent(),
                "title" => $notification->getTitle(),
                "sound" => "default",
                "message" => $notification->getContent(),
                "store_id" => $notification->getStoreId(),
                "banner_url" => $bannerUrl,
                "notificationType" => $notification->getType()
            ];
            if ($notification->getType() == "category" && $notification->getProCatId() != "") {
                // for category /////////////////////////////////////////////////////
                $message["categoryName"] = $this->categoryResource->getAttributeRawValue(
                    $notification->getProCatId(),
                    "name",
                    1
                );
                $message["categoryId"] = $notification->getProCatId();
            } elseif ($notification->getType() == "product" && $notification->getProCatId() != "") {
                // for product //////////////////////////////////////////////////////
                $message["productName"] = $this->productResource->getAttributeRawValue(
                    $notification->getProCatId(),
                    "name",
                    1
                );
                $message["productId"] = $notification->getProCatId();
            }
            $url = "https://fcm.googleapis.com/fcm/send";
            $authKey = $this->helper->getConfigData("mobikul/notification/apikey");
            $headers = [
                "Authorization" => "key=".$authKey,
                "Content-Type" => "application/json"
            ];
            // for android //////////////////////////////////////////////////////////
            $topic = $this->helper->getConfigData("mobikul/notification/andoridtopic");
            $fields = [
                "to" => "/topics/".$topic,
                "data" => $message,
                "priority" => "high",
                "time_to_live" => 30,
                "delay_while_idle" => true,
                "content_available" => true
            ];
            $this->curl->setHeaders($headers);
            $this->curl->post($url, json_encode($fields));
            $result = $this->curl->getBody();
            if ($this->isJson($result)) {
                $this->messageManager->addSuccess(__("Notification pushed successfully for android."));
            } else {
                $this->messageManager->addError(__("Sorry something went wrong for android."));
            }
            // for ios //////////////////////////////////////////////////////////////
            $topic = $this->helper->getConfigData("mobikul/notification/iostopic");
            $fields = [
                "to" => "/topics/".$topic,
                "data" => $message,
                "priority" => "high",
                "time_to_live" => 30,
                "notification" => $message,
                "delay_while_idle" => true,
                "content_available" => true
            ];
            $this->curl->setHeaders($headers);
            $this->curl->post($url, json_encode($fields));
            $result = $this->curl->getBody();
            if ($this->isJson($result)) {
                $this->messageManager->addSuccess(__("Notification pushed successfully for iOS."));
            } else {
                $this->messageManager->addError(__("Sorry something went wrong for iOS."));
            }
        } else {
            $this->messageManager->addSuccess(__("Invalid Notification."));
        }
        return $resultRedirect->setPath("mobikul/notification/edit", ["id"=>$this->initCurrentNotification()]);
    }

    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    protected function initCurrentNotification()
    {
        return (int)$this->getRequest()->getParam("id");
    }
}
