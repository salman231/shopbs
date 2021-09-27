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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Featuredcategories;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Webkul\MobikulCore\Block\Adminhtml\Edit\GenericButton;

/**
 * Class SaveButton. block
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Function to get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [
            "label" => __("Save Featured Category"),
            "class" => "save primary",
            "data_attribute" => [
                "mage-init" => ["button"=>["event"=>"save"]],
                "form-role" => "save"
            ],
            "sort_order" => 90
        ];
        return $data;
    }
}
