<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Block\Adminhtml\System;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Snippet
 * @package Mageplaza\DailyDeal\Block\Adminhtml\System
 */
class Snippet extends Field
{
    /**
     * @var string
     */
    protected $_template = 'system/js.phtml';

    /**
     * Unset scope
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = '<div class="control-value" style="padding-top: 8px">';
        $html .= '<p>' . __('Use the following code to show the daily deal block at any place you want') . '</p>';
        $html .= '<pre style="background-color: #f5f5dc"><code>{{block class="Mageplaza\DailyDeal\Block\Widget" type="feature" limit="5" display="slider" title="Feature Deal"}}</code></pre>';
        $html .= '<div class="section"><h3>' . __('List of "type" attributes available: all, feature, new, upcoming, bestseller, random') . '</h3>';

        return $html . $this->_toHtml();
    }
}
