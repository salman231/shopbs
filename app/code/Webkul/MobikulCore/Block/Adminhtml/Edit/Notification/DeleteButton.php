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
 * Class Delete Button block
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
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
                "label" => __("Delete Notification"),
                "class" => "delete",
                "id" => "notification-edit-delete-button",
                "data_attribute" => ["url" => $this->getDeleteUrl()],
                "on_click" => "",
                "sort_order" => 20
            ];
        }
        return $data;
    }

    /**
     * Function to get delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl("*/*/delete", ["id" => $this->getNotificationId()]);
    }
}
