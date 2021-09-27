<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Block\Adminhtml\Payment\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class General extends Generic implements TabInterface{

    protected $_store;
    protected $_paymentConfig;
    protected $_groupManagement;
    protected $_converter;
    protected $_customerGroupsArray;

    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    protected $_initialConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $store,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter,
        \Magento\Framework\App\Config\Initial $initialConfig,
        array $data = []

    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_store = $store;
        $this->_paymentConfig = $paymentConfig;
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
        $this->_initialConfig = $initialConfig;
    }

    public function getTabLabel()
    {
        return __('Payment Methods');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Payment Methods');
    }

    /**
     * Returns status flag about this tab can be showed or not
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

    public function getCustomerGroupsArray()
    {
        if (!$this->_customerGroupsArray) {
            $groups = array_merge(
                [$this->_groupManagement->getNotLoggedInGroup()],
                $this->_groupManagement->getLoggedInGroups()
            );
            $this->_customerGroupsArray = $this->_converter->toOptionArray($groups, 'id', 'code');
        }
        return $this->_customerGroupsArray;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry(\Amasty\Methods\Controller\Adminhtml\RegistryConstants::CURRENT_AMASTY_METHODS_PAYMENT);

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('amasty_methods_');

        $fieldset = $form->addFieldset('scope_fieldset', ['legend' => __('Scopes')]);

        $fieldset->addField(
            'website_id',
            'select',
            [
                'label' => __('Current Scope'),
                'title' => __('Current Scope'),
                'name' => 'website_id',
                'required' => true,
                'options' => $this->_store->getWebsiteOptionHash(true)
            ]
        );

        $fieldset = $form->addFieldset('methods_fieldset', ['legend' => __('Payment Methods')]);

        $fieldset->addField(
            \Amasty\Methods\Model\Structure::VAR_RESTRICT_METHOD,
            'select',
            [
                'label' => __('Action for selected groups'),
                'title' => __('Action for selected groups'),
                'name' => \Amasty\Methods\Model\Structure::VAR_RESTRICT_METHOD,
                'options' => [0 => __('Allow'), 1 => __('Deny')]
            ]
        );

        $methods = $this->_initialConfig->getData('default')['payment'];;

        foreach($methods as $methodCode => $method) {
            if (isset($method['title'])) {
                $fieldset->addField(
                    'payment_method_' . $methodCode,
                    'multiselect',
                    [
                        'label' => $method['title'],
                        'title' => $method['title'],
                        'name' => $model->getObjectCode() . '[' . $methodCode . ']',
                        'values' => $this->getCustomerGroupsArray()
                    ]
                );
            }
        }

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        $html .= "<script>
            require(['prototype'], function(){
                $('amasty_methods_website_id').on('change', function(){
                    jQuery('body').trigger('processStart');
                    document.location.href = '" . $this->getUrl('amasty_methods/payment/index', [
                'website_id' => '_website_id'
            ]) . "'.replace('_website_id', this.value);
                })
            })
        </script>";
        return $html;
    }

}
