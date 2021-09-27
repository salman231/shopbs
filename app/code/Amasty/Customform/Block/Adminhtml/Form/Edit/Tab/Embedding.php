<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */


namespace Amasty\Customform\Block\Adminhtml\Form\Edit\Tab;

/**
 * Form page edit form Embedding tab
 */
class Embedding extends AbstractTab
{
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

        if ($model && $model->getId()) {
            $this->generateEmbeddingContent($model);
            $fieldset = $form->addFieldset('embedding_cms_form', ['legend' => __('Cms Pages')]);
            $fieldset->addField(
                'cms',
                'textarea',
                [
                    'name' => 'cms',
                    'label' => __('Cms Embedding Code'),
                    'title' => __('Cms Embedding Code'),
                    'note' => __('Copy this code into CMS Page Editor to insert form into any CMS page'),
                    'readonly' => true
                ]
            );

            $fieldsetT = $form->addFieldset('embedding_template_form', ['legend' => __('Templates')]);
            $fieldsetT->addField(
                'template',
                'textarea',
                [
                    'name' => 'template',
                    'label' => __('Template Embedding Code'),
                    'title' => __('Template Embedding Code'),
                    'note' => __('Insert this code into *.phtml template directly to display form in any block.'),
                    'readonly' => true
                ]
            );

            $fieldsetL = $form->addFieldset('embedding_layout_form', ['legend' => __('Layout Updates')]);
            $fieldsetL->addField(
                'layout',
                'textarea',
                [
                    'name' => 'layout',
                    'label' => __('Layout Embedding Code'),
                    'title' => __('Layout Embedding Code'),
                    'note' => __('Insert this code into layout to display the form.'),
                    'readonly' => true
                ]
            );
        } else {
            $form->addFieldset('embedding_noexist_form', [
                'legend' => __('This form is not saved yet. Please save this form first to get your embedding codes.')
            ]);
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        parent::_prepareForm();
        return $this;
    }

    private function generateEmbeddingContent(\Amasty\Customform\Model\Form &$model)
    {
        $cms = '{{widget type="Amasty\Customform\Block\Init" ' .
            'template="init.phtml" form_id="' . $model->getId() . '"}}';
        $model->setCms($cms);

        $template = '<?= $this->helper("Amasty\Customform\Helper\Data")->renderForm("' .
            $model->getId() . '") ?>';
        $model->setTemplate($template);

        $layout = '<referenceContainer name="content">
            <block class="Amasty\Customform\Block\Init" name="amasty.customform.init.' . $model->getId() . '">
                <arguments>
                    <argument name="form_id" xsi:type="string">' . $model->getId() . '</argument>
                </arguments>
            </block>
        </referenceContainer>';
        $model->setLayout($layout);
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Embedding');
    }
}
