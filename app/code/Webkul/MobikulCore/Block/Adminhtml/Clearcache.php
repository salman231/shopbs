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

namespace Webkul\MobikulCore\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Clearcache to manage the Cache process in the module
 */
class Clearcache extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = "Webkul_MobikulCore::system/config/button.phtml";

    /**
     * Function render
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element element
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Function _getElementHtml
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element element
     *
     * @return Html
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Function getClearCacheUrl
     *
     * @return URL
     */
    public function getClearCacheUrl()
    {
        return $this->getUrl("mobikul/clearcache/index");
    }

    /**
     * Function getButtonHtml
     *
     * @return html
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setData(
                [
                "id" => "clear-cache",
                "label" => __("Clear cache"),
                ]
            );
        return $button->toHtml();
    }
}
