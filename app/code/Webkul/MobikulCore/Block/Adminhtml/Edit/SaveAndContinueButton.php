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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveAndContinueButton block
 */
class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Function to get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [
            "label" => __("Save and Continue Edit"),
            "class" => "save",
            "sort_order" => 80,
            "data_attribute" => ["mage-init"=>["button"=>["event"=>"saveAndContinueEdit"]]]
        ];
        return $data;
    }
}
