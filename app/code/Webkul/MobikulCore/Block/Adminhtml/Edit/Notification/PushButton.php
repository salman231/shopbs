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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Notification;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Webkul\MobikulCore\Block\Adminhtml\Edit\GenericButton;

/**
 * Class to create push button on notification Page
 */
class PushButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Fucntion to get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $notificationId = $this->getNotificationId();
        $data = [];
        if ($notificationId) {
            $data = [
                "label" => __("Push Notification"),
                "class" => "save primary",
                "id" => "notification-push-button",
                "data_attribute" => ["url"=>$this->getPushUrl()],
                "on_click" => "location.href = '".$this->getPushUrl()."'",
                "sort_order" => 90
            ];
        }
        return $data;
    }

    /**
     * Function to get url for push button
     *
     * @return string
     */
    public function getPushUrl()
    {
        return $this->getUrl("*/*/push", ["id"=>$this->getNotificationId()]);
    }
}
