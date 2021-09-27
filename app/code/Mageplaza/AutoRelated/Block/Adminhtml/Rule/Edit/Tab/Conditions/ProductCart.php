<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Conditions;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Mageplaza\AutoRelated\Model\RuleFactory;

/**
 * Class ProductCart
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Conditions
 */
class ProductCart extends Generic implements TabInterface
{
    /**
     * @var RuleFactory
     */
    protected $autoRelatedRuleFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * @var Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var Conditions
     */
    protected $conditions;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Conditions $conditions
     * @param Fieldset $rendererFieldset
     * @param RuleFactory $autoRelatedRuleFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Conditions $conditions,
        Fieldset $rendererFieldset,
        RuleFactory $autoRelatedRuleFactory,
        array $data = []
    ) {
        $this->rendererFieldset       = $rendererFieldset;
        $this->conditions             = $conditions;
        $this->autoRelatedRuleFactory = $autoRelatedRuleFactory;
        $this->registry               = $registry;
        $this->formKey                = $context->getFormKey();

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('autorelated_rule');
        /** @var Form $form */
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param RuleFactory $model
     * @param string $fieldsetId
     * @param string $formName
     *
     * @return Form
     * @throws LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'conditions_fieldset', $formName = 'autorelated_rule_form')
    {
        $id = $this->getRequest()->getParam('id');
        if (!$model) {
            $model = $this->autoRelatedRuleFactory->create();
            $model->load($id);
        }
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $newChildUrl = $this->getUrl(
            'mparp/condition/newConditionHtml/form/' . $model->getConditionsFieldSetId($formName),
            ['form_namespace' => $formName]
        );

        $renderer = $this->rendererFieldset->setTemplate('Mageplaza_AutoRelated::rule/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($model->getConditionsFieldSetId($formName));
        if ($model->getBlockType() == 'product') {
            $renderer->setAjaxUrl($this->getUrl(
                'mparp/grid/productlist',
                ['id' => $id, 'type' => 'cond', 'form_key' => $this->formKey->getFormKey()]
            ));
        }

        $fieldset = $form->addFieldset($fieldsetId, [
            'legend' => __('Apply the rule only if the following conditions are met (leave blank for all products).')
        ])->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', [
            'name'           => 'conditions',
            'label'          => __('Conditions'),
            'title'          => __('Conditions'),
            'required'       => true,
            'data-form-part' => $formName
        ])
            ->setRule($model)
            ->setRenderer($this->conditions);
        $form->setValues($model->getData());
        $model->getConditions()->setJsFormObject($model->getConditionsFieldSetId($formName));
        $this->setConditionFormName($model->getConditions(), $formName);

        return $form;
    }

    /**
     * Prepare content for tab
     *
     * @return Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isAjaxLoaded()
    {
        return false;
    }
}
