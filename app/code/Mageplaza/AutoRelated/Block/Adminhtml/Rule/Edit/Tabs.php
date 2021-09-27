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

namespace Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

/**
 * Class Tabs
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Tabs constructor.
     *
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);

        $this->registry = $registry;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('autorelated_rule_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Related Block Rule'));
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $id = $this->getRequest()->getParam('id');
        $this->addTab('main', [
            'label'   => __('Rule Information'),
            'title'   => __('Rule Information'),
            'content' => $this->getChildHtml('main'),
            'active'  => true
        ]);

        $conditionAlias = ($this->registry->registry('autorelated_type') == 'category') ? 'category_conditions' : 'conditions';
        $this->addTab('labels', [
            'label'   => __('Products to Meet Conditions'),
            'title'   => __('Products to Meet Conditions'),
            'content' => $this->getChildHtml($conditionAlias)
        ]);

        $this->addTab('actions', [
            'label'   => __('Products to Show'),
            'title'   => __('Products to Show'),
            'content' => $this->getChildHtml('actions')
        ]);

        $rule = $this->registry->registry('autorelated_rule');
        if ($rule && $id && !$this->registry->registry('autorelated_test_add') && $rule->hasChild()) {
            $this->addTab('test', [
                'label'   => __('A/B Testing'),
                'title'   => __('A/B Testing'),
                'content' => $this->getChildHtml('test')
            ]);
        }

        return parent::_beforeToHtml();
    }
}
