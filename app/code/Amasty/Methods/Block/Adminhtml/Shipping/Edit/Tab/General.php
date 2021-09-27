<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Block\Adminhtml\Shipping\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class General extends Generic implements TabInterface{

    protected $_store;
    protected $_shippingConfig;
    protected $_groupManagement;
    protected $_converter;
    protected $_customerGroupsArray;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $store,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_store = $store;
        $this->_shippingConfig = $shippingConfig;
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
    }

    public function getTabLabel()
    {
        return __('Shipping Methods');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Shipping Methods');
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
        $model = $this->_coreRegistry->registry(\Amasty\Methods\Controller\Adminhtml\RegistryConstants::CURRENT_AMASTY_METHODS_SHIPPING);

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

        $fieldset = $form->addFieldset('methods_fieldset', ['legend' => __('Shipping Methods')]);

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

        foreach($this->_shippingConfig->getAllCarriers() as $carrierCode => $carrier) {
            $title = $carrier->getConfigData('title') ? $carrier->getConfigData('title') : $carrierCode;

            $fieldset->addField(
                'shipping_method_' . $carrierCode,
                'multiselect',
                [
                    'label' => $title,
                    'title' => $title,
                    'name' => $model->getObjectCode() . '[' . $carrierCode . ']',
                    'values' => $this->getCustomerGroupsArray()
                ]
            );
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
                    document.location.href = '" . $this->getUrl('amasty_methods/shipping/index', [
                        'website_id' => '_website_id'
                    ]) . "'.replace('_website_id', this.value);
                })
            })
        </script>";
        return $html;
    }

}