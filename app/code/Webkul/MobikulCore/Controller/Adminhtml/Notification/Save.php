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

use Webkul\MobikulCore\Controller\RegistryConstants;

/**
 * Save Class controller
 */
class Save extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();
        $notificationId = isset(
            $originalRequestData["mobikul_notification"]["id"]
        ) ? $originalRequestData["mobikul_notification"]["id"] : null;
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($originalRequestData) {
            try {
                $notificationData = $originalRequestData["mobikul_notification"];
                $notificationData["filename"] = $this->getNotificationImageName($notificationData);
                $notificationData["store_id"] = $this->getNotificationStoreId($notificationData);
                $request = $this->getRequest();
                $isExistingNotification = (bool) $notificationId;
                $notification = $this->notificationDataFactory->create();
                if ($isExistingNotification) {
                    $currentNotification = $this->notificationRepository->getById($notificationId);
                    $notificationData["id"] = $notificationId;
                }
                $notificationData["updated_at"] = $this->date->gmtDate();
                if (!$isExistingNotification) {
                    $notificationData["created_at"] = $this->date->gmtDate();
                }
                if (in_array($notificationData["type"], ["custom","other"])) {
                    $notificationData["pro_cat_id"] = "";
                }
                if ($notificationData["type"] == "custom") {
                    if (isset($notificationData["collection_type"]) &&
                        $notificationData["collection_type"] == "product_attribute"
                    ) {
                        $attribute = $notificationData["attribute"] ?? [];
                        if (count($attribute) > 0) {
                            $notificationData["filter_data"] = $this->serializer->serialize($attribute);
                        } elseif (empty($notificationData["filter_data"])) {
                            $this->messageManager->addError(__("Please choose product attribute."));
                            $this->_getSession()->setNotificationFormData($originalRequestData);
                            $resultRedirect->setPath(
                                "mobikul/notification/edit",
                                ["id"=>$notificationId, "_current"=>true]
                            );
                            return $resultRedirect;
                        }
                    }
                    if (isset($notificationData["collection_type"]) &&
                        $notificationData["collection_type"] == "product_ids"
                    ) {
                        $productIds = $notificationData["productIds"] ?? "";
                        if ($productIds == "" && empty($notificationData["filter_data"])) {
                            $this->messageManager->addError(__("Please provide few product ids."));
                            $this->_getSession()->setNotificationFormData($originalRequestData);
                            $resultRedirect->setPath(
                                "mobikul/notification/edit",
                                ["id"=>$notificationId, "_current"=>true]
                            );
                            return $resultRedirect;
                        } elseif ($productIds != "") {
                            $notificationData["filter_data"] = $this->serializer->serialize($productIds);
                        }
                    }
                    if (isset($notificationData["collection_type"]) &&
                        $notificationData["collection_type"] == "product_new"
                    ) {
                        $newProductCount = $notificationData["newProductCount"] ?? "";
                        if ($newProductCount == "" && empty($notificationData["filter_data"])) {
                            $this->messageManager->addError(__("Please provide product count."));
                            $this->_getSession()->setNotificationFormData($originalRequestData);
                            $resultRedirect->setPath(
                                "mobikul/notification/edit",
                                ["id"=>$notificationId, "_current"=>true]
                            );
                            return $resultRedirect;
                        } elseif ($newProductCount != "") {
                            $notificationData["filter_data"] = $this->serializer->serialize($newProductCount);
                        }
                    }
                } else {
                    $notificationData["collection_type"] = "";
                    $notificationData["filter_data"] = $this->serializer->serialize([]);
                }
                $notification->setData($notificationData);
                // Save notification ////////////////////////////////////////////////
                if ($isExistingNotification) {
                    $this->notificationRepository->save($notification);
                } else {
                    $notification = $this->notificationRepository->save($notification);
                    $notificationId = $notification->getId();
                }
                $this->_getSession()->unsNotificationFormData();
                // Done Saving notification, finish save action /////////////////////
                $this->coreRegistry->register(RegistryConstants::CURRENT_NOTIFICATION_ID, $notificationId);
                $this->messageManager->addSuccess(__("You saved the notification."));
                $returnToEdit = (bool) $this->getRequest()->getParam("back", false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setNotificationFormData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException(
                    $exception,
                    __("Something went wrong while saving the notification. %1", $exception->getMessage())
                );
                $this->_getSession()->setNotificationFormData($originalRequestData);
                $returnToEdit = true;
            }
        }
        if ($returnToEdit) {
            if ($notificationId) {
                $resultRedirect->setPath("mobikul/notification/edit", ["id"=>$notificationId, "_current"=>true]);
            } else {
                $resultRedirect->setPath("mobikul/notification/new", ["_current"=>true]);
            }
        } else {
            $resultRedirect->setPath("mobikul/notification/index");
        }
        return $resultRedirect;
    }

    private function getNotificationImageName($notificationData)
    {
        if (isset($notificationData["filename"][0]["name"])) {
            if (isset($notificationData["filename"][0]["name"])) {
                return $notificationData["filename"] = $notificationData["filename"][0]["name"];
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__("Please upload notification image."));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__("Please upload notification image."));
        }
    }

    private function getNotificationStoreId($notificationData)
    {
        if (isset($notificationData["store_id"])) {
            return $notificationData["store_id"] = implode(",", $notificationData["store_id"]);
        } else {
            return $notificationData["store_id"] = 0;
        }
    }
}
