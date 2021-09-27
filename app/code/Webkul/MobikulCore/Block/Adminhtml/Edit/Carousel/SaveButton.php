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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Carousel;

use Webkul\MobikulCore\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Save Button to create save button for form
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Function get Button Data
     *
     * @return array $data
     */
    public function getButtonData()
    {
        $data = [
            "label" => __("Save Carousel"),
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
