<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Block\Adminhtml\Edit\Deliveryboy;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Webkul\DeliveryBoy\Block\Adminhtml\Edit\GenericButton;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $deliveryboyId = $this->getDeliveryboyId();
        $data = [];
        if ($deliveryboyId) {
            $data = [
                "id" => "deliveryboy-edit-delete-button",
                "label" => __("Delete DeliveryBoy"),
                "class" => "delete",
                "on_click" => "",
                "sort_order" => 20,
                "data_attribute" => ["url"=>$this->getDeleteUrl()]
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl("*/*/delete", ["id" => $this->getDeliveryboyId()]);
    }
}
