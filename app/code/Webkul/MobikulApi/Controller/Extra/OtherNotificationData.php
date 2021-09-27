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

class OtherNotificationData extends AbstractMobikul
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            if ($this->notificationId > 0) {
                $notification = $this->mobikulNotification->create()->load($this->notificationId);
                $this->returnArray["title"] = $notification->getTitle();
                $this->returnArray["content"] = $notification->getContent();
            } else {
                $this->returnArray["message"] = __("Invalid Notification Id");
                return $this->getJsonResponse($this->returnArray);
            }
            $this->returnArray["success"] = true;
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->notificationId = $this->wholeData["notificationId"] ?? 0;
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}
