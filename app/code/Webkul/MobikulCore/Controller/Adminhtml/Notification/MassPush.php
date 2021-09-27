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
 * MassPush Class controller
 */
class MassPush extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $authKey = $this->helper->getConfigData("mobikul/notification/apikey");
        $androidTopic = $this->helper->getConfigData("mobikul/notification/andoridtopic");
        $iosTopic = $this->helper->getConfigData("mobikul/notification/iostopic");
        if ($authKey == "") {
            $this->messageManager->addError(__("Please add Notification API key in Configuration."));
            return $resultRedirect->setPath("mobikul/notification/index");
        }
        if ($androidTopic == "") {
            $this->messageManager->addError(__("Please add Android Topic in Configuration."));
            return $resultRedirect->setPath("mobikul/notification/index");
        }
        if ($iosTopic == "") {
            $this->messageManager->addError(__("Please add IOS Topic in Configuration."));
            return $resultRedirect->setPath("mobikul/notification/index");
        }
        foreach ($collection->getAllIds() as $key => $notificationId) {
            $model = $this->notificationRepository->getById($notificationId);
            $baseTmpPath = "mobikul/notification/";
            $target = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).$baseTmpPath;
            $bannerUrl = "";
            if ($model->getFilename() != "") {
                $bannerUrl = $target.$model->getFilename();
            }
            try {
                $message = [
                    "id" => $model->getId(),
                    "body" => $model->getContent(),
                    "sound" => "default",
                    "title" => $model->getTitle(),
                    "message" => $model->getContent(),
                    "store_id" => $model->getStoreId(),
                    "banner_url" => $bannerUrl,
                    "notificationType" => $model->getType()
                ];
                if ($model->getType() == "category" && $model->getProCatId() != "") {
                    // for category /////////////////////////////////////////////////
                    $message["categoryId"] = $model->getProCatId();
                    $message["categoryName"] = $this->categoryResource->getAttributeRawValue(
                        $model->getProCatId(),
                        "name",
                        1
                    );
                } elseif ($model->getType() == "product" && $model->getProCatId() != "") {
                    // for product //////////////////////////////////////////////////
                    $message["productId"] = $model->getProCatId();
                    $message["productName"] = $this->productResource->getAttributeRawValue(
                        $model->getProCatId(),
                        "name",
                        1
                    );
                }
                $url = "https://fcm.googleapis.com/fcm/send";
                $authKey = $this->helper->getConfigData("mobikul/notification/apikey");
                $headers = [
                    "Authorization" => "key=".$authKey,
                    "Content-Type" => "application/json"
                ];
                // for android //////////////////////////////////////////////////////
                $fields = [
                    "to" => "/topics/".$androidTopic,
                    "data" => $message,
                    "priority" => "high",
                    "time_to_live" => 30,
                    "delay_while_idle" => true,
                    "content_available" => true
                ];
                $this->curl->setHeaders($headers);
                $this->curl->post($url, json_encode($fields));
                $androidresult = $this->curl->getBody();
                // for ios //////////////////////////////////////////////////////////
                $fields = [
                    "to" => "/topics/".$iosTopic,
                    "data" => $message,
                    "priority" => "high",
                    "time_to_live" => 30,
                    "notification" => $message,
                    "delay_while_idle" => true,
                    "content_available" => true
                ];
                $this->curl->setHeaders($headers);
                $this->curl->post($url, json_encode($fields));
                $iosresult = $this->curl->getBody();
                if (!$this->isJson($iosresult) || !$this->isJson($androidresult)) {
                    $this->messageManager->addError(__("Sorry something went wrong."));
                    return $resultRedirect->setPath("mobikul/notification/index");
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->messageManager->addSuccess(
            __("Total %1 notification(s) has been pushed successfully.", count($collection))
        );
        return $resultRedirect->setPath("mobikul/notification/index");
    }

    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::notification");
    }
}
