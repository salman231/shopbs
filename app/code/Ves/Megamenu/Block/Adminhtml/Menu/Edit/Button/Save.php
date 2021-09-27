<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Block\Adminhtml\Menu\Edit\Button;

use Magento\Ui\Component\Control\Container;

class Save extends \Magento\Backend\Block\Widget\Container
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context  
     * @param \Magento\Framework\Registry           $registry 
     * @param array                                 $data     
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Megamenu\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $registry;
        $this->helper        = $helper;
    }

    /**
     * Prepare button and grid
     *
     * @return \Lof\FollowUpEmail\Block\Adminhtml\Email
     */
    protected function _prepareLayout()
    {
        $this->buttonList->remove('save');
        if ($this->_isAllowedAction('Ves_Megamenu::menu_save')) {
            $addButtonProps = [
                'id'           => 'save',
                'label'        => __('Save Menu'),
                'class'        => 'add',
                'button_class' => '',
                'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
                'options'      => $this->getOptions()
            ];
            $this->buttonList->add('add_new', $addButtonProps);
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = [];

        if ($this->helper->getConfig('general_settings/enable_cache')) {
            $options[] = [
                'id' => 'cache',
                'label' => __('Save & Flush Menu Cache')
            ];
        }

        $options[] = [
            'id' => 'new-button',
            'label' => __('Save & New'),
            'data_attribute' => [
                'mage-init' => [
                    'button' => [
                        'event' => 'saveAndNew',
                        'target' => '#edit_form'
                    ]
                ]
            ]
        ];

        if ($this->_coreRegistry->registry('megamenu_menu')->getId()) {
            $options[] = [
                'id' => 'duplicate-button',
                'label' => __('Save & Duplicate'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndDuplicate',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ];
        }

        return $options;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

}
