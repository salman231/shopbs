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

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [
            "label" => __("Save DeliveryBoy"),
            "class" => "save primary",
            "data_attribute" => [
                "mage-init" => ["button"=>["event"=>"save"]],
                "form-role" => "save",
            ],
            "sort_order" => 90
        ];
        return $data;
    }
}
