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
 * MassDisable Class controller
 */
class MassDisable extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $coditionArr = [];
        $resultRedirect = $this->resultRedirectFactory->create();
        $notificationsUpdated = 0;
        foreach ($collection->getAllIds() as $key => $notificationId) {
            $currentNotification = $this->notificationRepository->getById($notificationId);
            $notificationData    = $currentNotification->getData();
            if (count($notificationData)) {
                $condition = "`id`=".$notificationId;
                array_push($coditionArr, $condition);
                $notificationsUpdated++;
            }
        }
        $coditionData = implode(" OR ", $coditionArr);
        $collection->setNotificationData($coditionData, ["status"=>0, "updated_at"=>$this->date->gmtDate()]);
        if ($notificationsUpdated) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were disabled.", $notificationsUpdated));
        }
        return $resultRedirect->setPath("mobikul/notification/index");
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::notification");
    }
}
