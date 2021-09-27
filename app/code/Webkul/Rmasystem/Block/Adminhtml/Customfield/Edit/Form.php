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
namespace Webkul\Rmasystem\Block\Adminhtml\Customfield\Edit;

/**
 * Rmasystem reason edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customfield_form');
        $this->setTitle(__('Custom Field Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Webkul\Rmasystem\Model\Reason $model */
        $model = $this->_coreRegistry->registry('rmasystem_customfield');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' =>
                [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype'=>'multipart/form-data'
                ]
            ]
        );

        $form->setHtmlIdPrefix('customfield_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Field Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
                
        $fieldset->addField(
            'label',
            'text',
            ['name' => 'label', 'label' => __('Field Label'), 'title' => __('Field Label'), 'required' => true]
        );

        $fieldset->addField(
            'inputname',
            'text',
            ['name' => 'inputname', 'label' => __('Field Code'), 'title' => __('Field Code'), 'required' => true]
        );
        
        $fieldset->addField(
            'input_type',
            'select',
            [
                'label' => __('Type'),
                'title' => __('Type'),
                'name' => 'input_type',
                'required' => true,
                'options' => ["" => __("Please Select"),
                                "text" => __("Text"),
                                "textarea" => __("Text Area"),
                                "select" => __("Dropdown"),
                                "multiselect" => __("Multiple Select"),
                                "radio" =>__("Radio Button"),
                                "checkbox" => __("Check Box")
                            ]
            ]
        );

        $fieldset->addField(
            'sort',
            'text',
            [
                'name' => 'sort',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => true,
                'class' => 'validate-digits'
            ]
        );

        $fieldset->addField(
            'required',
            'select',
            [
                'label' => __('Value Required'),
                'title' => __('Value Required'),
                'name' => 'required',
                'options' => [
                        "1" => __("Yes"),
                        "0" => __("No")
                ]
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );
        if (!$model->getId()) {
            $model->setData('status', '1');
        }
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
