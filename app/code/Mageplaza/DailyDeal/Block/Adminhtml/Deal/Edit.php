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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Block\Adminhtml\Deal;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Mageplaza\DailyDeal\Block\Adminhtml\Deal
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve text for header element depending on loaded Deal
     *
     * @return string
     */
    public function getHeaderText()
    {
        $deal = $this->getDeal();
        if ($deal->getId()) {
            return __("Edit Deal '%1'", $this->escapeHtml($deal->getProductName()));
        }

        return __('Create Deal');
    }

    /**
     * Get Deal
     *
     * @return mixed
     */
    public function getDeal()
    {
        return $this->_coreRegistry->registry('mageplaza_dailydeal_deal');
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'Mageplaza_DailyDeal';
        $this->_controller = 'adminhtml_deal';

        parent::_construct();

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class'          => 'save',
                'label'          => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            20
        );

        $this->buttonList->update('back', 'onclick', "setLocation('" . $this->getBackUrl() . "')");
    }
}
