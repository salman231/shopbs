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
namespace Webkul\DeliveryBoy\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [
            "label" => __("Save and Continue Edit"),
            "class" => "save",
            "data_attribute" => [
                "mage-init"=>[
                    "button"=>[
                        "event"=>"saveAndContinueEdit"
                    ]
                ]
            ],
            "sort_order" => 80
        ];
        return $data;
    }
}
