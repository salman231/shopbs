<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_FollowUpEmail
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Block\Adminhtml;

class DropdownMenu extends \Magento\Backend\Block\Widget\Container
{
    protected $urlBuilder;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context 
     * @param array                                 $data    
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        parent::__construct($context);
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * Prepare button and grid
     *
     * @return \Lof\FollowUpEmail\Block\Adminhtml\Email
     */
    protected function _prepareLayout()
    { 
        $addButtonProps = [
            'id'           => 'add_new_menu',
            'label'        => __('Add New Menu'),
            'class'        => 'add',
            'button_class' => '',
            'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options'      => $this->_getMenuButtonOptions()
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add Email' split button
     *
     * @return array
     */
    protected function _getMenuButtonOptions()
    { 
        $options = [];
        $options[] = [
                'label'   => __('Horizontal Menu'),
                'onclick' => "setLocation('" . $this->urlBuilder->getUrl('*/*/new', ['desktop_template' => 'horizontal']) . "')"
            ];

        $options[] = [
                'label'   => __('Vertical Left Menu'),
                'onclick' => "setLocation('" . $this->urlBuilder->getUrl('*/*/new', ['desktop_template' => 'vertical-left']) . "')"
            ];

        $options[] = [
                'label'   => __('Vertical Right Menu'),
                'onclick' => "setLocation('" . $this->urlBuilder->getUrl('*/*/new', ['desktop_template' => 'vertical-right']) . "')"
            ];

        $options[] = [
                'label'   => __('Accordion Menu'),
                'onclick' => "setLocation('" . $this->urlBuilder->getUrl('*/*/new', ['desktop_template' => 'accordion']) . "')"
            ];

        $options[] = [
                'label'   => __('Drill Down Menu'),
                'onclick' => "setLocation('" . $this->urlBuilder->getUrl('*/*/new', ['desktop_template' => 'drill']) . "')"
            ];

        return $options;
    }
}
