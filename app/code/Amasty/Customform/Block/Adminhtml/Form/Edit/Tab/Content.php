<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block\Adminhtml\Form\Edit\Tab;

use Magento\Config\Model\Config\Source\YesnoFactory;
use Magento\Backend\Block\Widget\Form\Element\Dependence;

class Content extends AbstractTab
{
    /**
     * @var YesnoFactory
     */
    private $yesnoFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        YesnoFactory $yesnoFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->yesnoFactory = $yesnoFactory;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Amasty\Customform\Model\Form $model */
        $model = $this->_coreRegistry->registry('amasty_customform_form');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('form_');

        $fieldset = $form->addFieldset('content_fieldset', ['legend' => __('Form Content')]);

        $fieldset->addField(
            'submit_button',
            'textarea',
            [
                'name' => 'submit_button',
                'label' => __('Submit Button'),
                'title' => __('Submit Button'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'success_message',
            'textarea',
            [
                'name' => 'success_message',
                'label' => __('Success Message'),
                'title' => __('Success Message')
            ]
        );

        $showPopup = $fieldset->addField(
            'popup_show',
            'select',
            [
                'label' => __('Show on Button Click'),
                'title' => __('Show on Button Click'),
                'note'  => __('Custom Form popup will be displayed after the button is clicked '),
                'name' => 'popup_show',
                'values'    => $this->yesnoFactory->create()->toOptionArray(),
            ]
        );

        $popupButton = $fieldset->addField(
            'popup_button',
            'text',
            [
                'name'    => 'popup_button',
                'label'   => __('Button Text'),
                'title'   => __('Button Text')
            ]
        );

        $data = $model->getData();
        if (empty($data['success_message']) && empty($data['form_id'])) {
            $data['success_message'] = __('Thanks for contacting us. Your request was saved successfully.');
        }
        if (empty($data['submit_button'])) {
            $data['submit_button'] = __('Submit');
        }
        if (empty($data['popup_button'])) {
            $data['popup_button'] = __('Show Form');
        }

        $dependence = $this->getLayout()->createBlock(Dependence::class);
        $this->addDependencies($dependence, $showPopup, $popupButton);
        $this->setChild('form_after', $dependence);

        $form->setValues($data);
        $this->setForm($form);

        parent::_prepareForm();
        return $this;
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Form Content');
    }
}
