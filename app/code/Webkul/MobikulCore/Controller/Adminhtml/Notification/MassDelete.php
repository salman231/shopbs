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

use Magento\Framework\Controller\ResultFactory;

/**
 * MassDelete Class controller
 */
class MassDelete extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $notificationsDeleted = 0;
        foreach ($collection->getAllIds() as $notificationId) {
            if (!empty($notificationId)) {
                try {
                    $this->notificationRepository->deleteById($notificationId);
                    $notificationsDeleted++;
                } catch (\Exception $exception) {
                    $this->messageManager->addError($exception->getMessage());
                }
            }
        }
        if ($notificationsDeleted) {
            $this->messageManager->addSuccess(__("A total of %1 record(s) were deleted.", $notificationsDeleted));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath("mobikul/notification/index");
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::notification");
    }
}
