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
 * Delete Class controller
 */
class Delete extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $isPost = $this->getRequest()->isPost();
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__("Notification could not be deleted."));
            return $resultRedirect->setPath("mobikul/notification/index");
        }
        $notificationId = $this->initCurrentNotification();
        if (!empty($notificationId)) {
            try {
                $this->notificationRepository->deleteById($notificationId);
                $this->messageManager->addSuccess(__("Notification has been deleted."));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }
        return $resultRedirect->setPath("mobikul/notification/index");
    }

    protected function initCurrentNotification()
    {
        $notificationId = (int)$this->getRequest()->getParam("id");
        if ($notificationId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_NOTIFICATION_ID, $notificationId);
        }
        return $notificationId;
    }
}
