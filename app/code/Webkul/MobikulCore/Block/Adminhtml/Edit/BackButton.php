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
 * Class BackButton block
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Function to get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            "label" => __("Back"),
            "on_click" => sprintf("location.href='%s';", $this->getBackUrl()),
            "class" => "back",
            "sort_order" => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl("*/*/");
    }
}
