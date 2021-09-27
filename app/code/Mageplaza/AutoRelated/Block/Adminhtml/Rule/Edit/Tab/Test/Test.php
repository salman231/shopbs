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

namespace Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Test;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Mageplaza\AutoRelated\Model\RuleFactory;

/**
 * Class Test
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Test
 */
class Test extends Generic implements TabInterface
{
    /**
     * @var RuleFactory
     */
    protected $autoRelatedRule;

    /**
     * Test constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param RuleFactory $autoRelatedRule
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        RuleFactory $autoRelatedRule,
        array $data = []
    ) {
        $this->autoRelatedRule = $autoRelatedRule;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'rule/tab/test.phtml';

    /**
     * Return Tab label
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('A/B Testing');
    }

    /**
     * Return Tab title
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('A/B Testing');
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function initForm()
    {
        $form = $this->_formFactory->create();
        $form->addFieldset('test_base_fieldset', ['legend' => __('A/B Testing')]);
        $this->setForm($form);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                'Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Test\BlockList',
                'autorelated.test.blocklist.grid'
            )
        );
        parent::_prepareLayout();

        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->initForm();

        return parent::_toHtml();
    }

    /**
     * Get Impression, Clicks, CTR
     *
     * @return array
     */
    public function getParameter()
    {
        $ruleId     = $this->getRequest()->getParam('id');
        $collection = $this->autoRelatedRule->create()->getCollection()->addFieldToFilter(
            ['rule_id', 'parent_id'],
            [
                ['eq' => $ruleId],
                ['eq' => $ruleId]
            ]
        );

        foreach ($collection as $item) {
            $impression[] = $item->getImpression();
            $clicks[]     = $item->getClick();
            $ctr[]        = $this->getCtr($item->getClick(), $item->getImpression());
        }

        return $parameter = [
            'impression' => $impression,
            'clicks'     => $clicks,
            'ctr'        => $ctr
        ];
    }

    /**
     * @param $click
     * @param $impression
     *
     * @return float|int
     */
    public function getCtr($click, $impression)
    {
        if ($impression) {
            return $click / $impression * 100;
        }

        return 0;
    }
}
