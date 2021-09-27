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
 * Index Class controller
 */
class Index extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::notification");
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Notifications"));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::notification");
    }
}
