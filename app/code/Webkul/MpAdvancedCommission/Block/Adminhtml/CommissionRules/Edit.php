<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Block\Adminhtml\CommissionRules;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var null
     */
    protected $_objectManager = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context     $context
     * @param \Magento\Framework\Registry               $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     * Initialize commission rules page edit block.
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Webkul_MpAdvancedCommission';
        $this->_controller = 'adminhtml_commissionRules';

        parent::_construct();

        if ($this->_isAllowedAction('Webkul_MpAdvancedCommission::commissionrules')) {
            $this->buttonList->update('save', 'label', __('Save Commission Rule'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ],
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }
        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element depending on loaded page.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('commission_rules')->getId()) {
            $id = $this->_coreRegistry->registry('commission_rules')->getId();
            return __("Edit Commission Rule for id '%1'", $this->escapeHtml($id));
        } else {
            return __('New Commission Rule');
        }
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later.
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'mpadvancedcommission/commissionrules/save',
            ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']
        );
    }
}
