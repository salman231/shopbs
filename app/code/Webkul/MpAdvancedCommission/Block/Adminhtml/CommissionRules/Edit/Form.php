<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Block\Adminhtml\CommissionRules\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as DataForm;

class Form extends Generic
{
    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var DataForm $form */
        $form = $this->_formFactory->create(
            ['data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'), 'method' => 'post'
                ]
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
