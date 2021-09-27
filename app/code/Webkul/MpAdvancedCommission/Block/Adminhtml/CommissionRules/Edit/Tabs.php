<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Block\Adminhtml\CommissionRules\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('commissionRules_form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Commission Rule Information'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'commissionRules_info',
            [
                'label' => __('Commission Rule'),
                'title' => __('Commission Rule'),
                'content' => $this->getChildHtml('commissionRules_info'),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
