<?php
/**
 * Dart_Productkeys Add Row Form Block.
 *
 */
namespace Dart\Productkeys\Block\Adminhtml\Productkeys;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class AddRow extends Container
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        $this->_objectId = 'row_id';
        $this->_blockGroup = 'Dart_Productkeys';
        $this->_controller = 'adminhtml_productkeys';
        parent::_construct();
        if ($this->_isAllowedAction('Dart_Productkeys::add_row')) {
            $this->buttonList->update('save', 'label', __('Save'));
        } else {
            $this->buttonList->remove('save');
        }
        $this->buttonList->remove('reset');
    }

    public function getHeaderText()
    {
        return __('Add RoW Data');
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     *
     * @return bool
     */
    public function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get form action URL.
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
			
            return $this->getData('form_action_url');
        }
		
        return $this->getUrl('*/*/save');
    }
}
