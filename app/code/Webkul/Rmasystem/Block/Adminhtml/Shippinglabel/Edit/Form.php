<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Block\Adminhtml\Shippinglabel\Edit;

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
        $this->setId('shippinglabel_form');
        $this->setTitle(__('Shipping Label Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Webkul\Rmasystem\Model\Reason $model */
        $model = $this->_coreRegistry->registry('rmasystem_shippinglabel');

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

        $form->setHtmlIdPrefix('shippinglabel_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Label Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'title',
            'text',
            ['name' => 'title', 'label' => __('Label Title'), 'title' => __('Label Title'), 'required' => true]
        );

        $fieldset->addType('image', \Webkul\Rmasystem\Block\Adminhtml\Shippinglabel\Helper\Form\Image::class);

        $fieldset->addField(
            'filename',
            'image',
            [
                'name' => 'filename',
                'label' => __('Label Image'),
                'title' => __('Label Image'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Label Price'),
                'title' => __('Label Price'),
                'required' => true,
                'class' => 'validate-number',
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
