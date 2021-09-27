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
use Webkul\MobikulCore\Api\Data\NotificationInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Edit Class controller
 */
class Edit extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $notificationId = $this->initCurrentNotification();
        $isExistingNotification = (bool)$notificationId;
        if ($isExistingNotification) {
            try {
                $notificationDirPath = $this->mediaDirectory->getAbsolutePath("mobikul/notification");
                if (!$this->fileDriver->isExists($notificationDirPath)) {
                    $this->fileDriver->createDirectory($notificationDirPath, 0777, true);
                }
                $baseTmpPath = "mobikul/notification/";
                $target = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ).$baseTmpPath;
                $notificationData = [];
                $notificationData["mobikul_notification"] = [];
                $notification = null;
                $notification = $this->notificationRepository->getById($notificationId);
                $result = $notification->getData();
                if (count($result)) {
                    $notificationData["mobikul_notification"] = $result;
                    $notificationData["mobikul_notification"]["filename"] = [];
                    $notificationData["mobikul_notification"]["filename"][0] = [];
                    $notificationData["mobikul_notification"]["filename"][0]["name"] = $result["filename"];
                    $notificationData["mobikul_notification"]["filename"][0]["url"] = $target.$result["filename"];
                    $filePath = $this->mediaDirectory->getAbsolutePath($baseTmpPath).$result["filename"];
                    if ($this->fileDriver->isExists($filePath)) {
                        $notificationData["mobikul_notification"]["filename"][0]["size"] =
                        $this->fileHelper->getFileSize($filePath);
                    } else {
                        $notificationData["mobikul_notification"]["filename"][0]["size"] = 0;
                    }
                    $notificationData["mobikul_notification"][NotificationInterface::ID] = $notificationId;
                    $this->_getSession()->setNotificationFormData($notificationData);
                } else {
                    $this->messageManager->addError(__("Requested notification doesn't exist"));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath("mobikul/notification/index");
                    return $resultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __("Something went wrong while editing the notification."));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath("mobikul/notification/index");
                return $resultRedirect;
            }
        }
        $resultPage = $this->resultPageFactory->create();
        $this->prepareDefaultNotificationTitle($resultPage);
        $resultPage->setActiveMenu("Webkul_MobikulCore::notification");
        if ($isExistingNotification) {
            $resultPage->getConfig()->getTitle()->prepend(__("Edit Item with id %1", $notificationId));
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__("New Notification"));
        }
        return $resultPage;
    }

    protected function initCurrentNotification()
    {
        $notificationId = (int)$this->getRequest()->getParam("id");
        if ($notificationId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_NOTIFICATION_ID, $notificationId);
        }
        return $notificationId;
    }

    protected function prepareDefaultNotificationTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__("Notification"));
    }
}
