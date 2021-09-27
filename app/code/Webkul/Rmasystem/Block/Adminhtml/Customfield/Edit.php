<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Block\Adminhtml\Customfield;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize rma customfield edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Webkul_Rmasystem';
        $this->_controller = 'adminhtml_customfield';

        parent::_construct();

        if ($this->_isAllowedAction('Webkul_Rmasystem::save')) {
            $this->buttonList->update('save', 'label', __('Save Custom Field'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('rmasystem_customfield')->getId()) {
            return __(
                "Edit Post '%1'",
                $this->escapeHtml(
                    $this->_coreRegistry->registry('rmasystem_customfield')->getTitle()
                )
            );
        } else {
            return __('New Field');
        }
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

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('rmasystem/customfield/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }

    public function getModel()
    {
        return $this->_coreRegistry->registry('rmasystem_customfield');
    }
    /**
     * Prepare form Html. Add block for configurable product modification interface.
     *
     * @return string
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->setTemplate('Webkul_Rmasystem::customfield/options.phtml')->toHtml();
        return $html;
    }
}
