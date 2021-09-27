<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2018 Atwix (https://www.atwix.com/)
 * @package Atwix_DynamicFields
 */
namespace Magemonkey\General\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
/**
 * Class AdditionalEmail
 */
class AdditionalEmail extends AbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn('categoryid', ['label' => __('Add Single Category Id'), 'class' => 'required-entry  validate-greater-than-zero']);
        // $this->addColumn('lastname', ['label' => __('Last Name')]);
        // $this->addColumn('email',['label' => __('Email'), 'size' => '50px', 'class' => 'required-entry validate-email']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Category');
    }
}