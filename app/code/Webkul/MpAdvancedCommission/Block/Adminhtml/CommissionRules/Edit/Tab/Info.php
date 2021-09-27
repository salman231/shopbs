<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Block\Adminhtml\CommissionRules\Edit\Tab;

use Webkul\MpAdvancedCommission\Model\CommissionRules;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Info extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Magento\Store\Model\System\Store       $systemStore
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('commissionRules_form');
        $this->setTitle(__('Commission Rule'));
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('commission_rules');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('commissionrules_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Commission Rule Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fieldset->addField(
            'commission_type',
            'select',
            [
                'label' => __('Commission Type'),
                'title' => __('Commission Type'),
                'name' => 'commission_type',
                'required' => true,
                'options' => [
                    CommissionRules::TYPE_FIXED => __('Fixed'),
                    CommissionRules::TYPE_PERCENT => __('Percent')
                ],
            ]
        );

        $fieldset->addField(
            'price_from',
            'text',
            [
                'name' => 'price_from',
                'label' => __('Product Price From'),
                'title' => __('Price From'),
                'required' => true,
                'class' => 'validate-greater-than-zero required-entry'
            ]
        );

        $fieldset->addField(
            'price_to',
            'text',
            [
                'name' => 'price_to',
                'label' => __('Product Price To'),
                'title' => __('Price To'),
                'required' => true,
                'class' => 'validate-greater-than-zero required-entry validate-max-min-check'
            ]
        );
        $fieldset->addField(
            'amount',
            'text',
            [
                'name' => 'amount',
                'label' => __('Commission'),
                'title' => __('Commission'),
                'required' => true,
                'class' => 'validate-greater-than-zero required-entry percent-amount-check',
                'note' => __('Commission Amount')
            ]
        );
        $this->setForm($form);
        return $this;
    }

    /**
     * Initialize form fileds values.
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $formData = $this->_coreRegistry->registry('commission_rules')->getData();
        
        if (!empty($formData)) {
            $formData['price_from'] = $this->numberFormat($formData['price_from']);
            $formData['price_to'] = $this->numberFormat($formData['price_to']);
            $formData['amount'] = $this->numberFormat($formData['amount']);
        }
        $this->getForm()->addValues($formData);

        return parent::_initFormValues();
    }

    /**
     * convert price to two decimal point
     *
     * @param float $price
     * @return string
     */
    public function numberFormat($price)
    {
        return str_replace(',', '', number_format($price, 2));
    }
}
