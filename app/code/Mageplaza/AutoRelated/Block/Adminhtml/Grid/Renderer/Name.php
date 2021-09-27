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
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

/**
 * Class Name
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Grid\Renderer
 */
class Name extends AbstractRenderer
{
    /**
     * Renders grid column
     *
     * @param DataObject $row
     *
     * @return  string
     */
    public function render(DataObject $row)
    {
        if ($row) {
            $name   = $row->getBlockName();
            $ruleId = $this->getRequest()->getParam('id');
            if ($row->getRuleId() == $ruleId) {
                $name .= __('(Parent)');
            }

            return $name;
        }

        return '';
    }
}
