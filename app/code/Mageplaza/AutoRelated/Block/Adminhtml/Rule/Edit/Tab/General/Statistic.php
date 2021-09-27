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

namespace Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\General;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Mageplaza\AutoRelated\Helper\Data;
use Mageplaza\AutoRelated\Model\RuleFactory;

/**
 * Class Statistic
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\General
 */
class Statistic extends Template
{
    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'rule/tab/statistic.phtml';

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Data
     */
    protected $autoRelatedHelper;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Statistic constructor.
     *
     * @param Context $context
     * @param RuleFactory $ruleFactory
     * @param Data $autoRelatedHelper
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        RuleFactory $ruleFactory,
        Data $autoRelatedHelper,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->ruleFactory       = $ruleFactory;
        $this->autoRelatedHelper = $autoRelatedHelper;
        $this->registry          = $registry;
    }

    /**
     * Load Rule
     *
     * @return  object
     */
    private function getRule()
    {
        $id   = $this->getRequest()->getParam('id');
        $test = $this->registry->registry('autorelated_test_add');
        if ($id && !$test) {
            return $this->ruleFactory->create()->load($id);
        }
    }

    /**
     * Get Ctr by impression and click
     *
     * @param int $impression
     * @param int $click
     *
     * @return  string
     */
    public function getCtr($impression, $click)
    {
        if ($impression && $click) {
            return $this->autoRelatedHelper->getCtr($click, $impression);
        }

        return '0%';
    }

    /**
     * Get Testing Rule
     *
     * @return bool
     */
    public function getChildInfo()
    {
        $rule = $this->getRule();
        if ($rule && $rule->hasChild()) {
            return $rule->getChild();
        }

        return false;
    }

    /**
     * Get Total Impression
     *
     * @return  int
     */
    public function getTotalImpression()
    {
        if ($this->getRule()) {
            return (int) $this->getRule()->getTotalImpression();
        }

        return 0;
    }

    /**
     * Get Total Click
     *
     * @return  int
     */
    public function getTotalClick()
    {
        if ($this->getRule()) {
            return (int) $this->getRule()->getTotalClick();
        }

        return 0;
    }

    /**
     * Get Current Impression
     *
     * @return  int
     */
    public function getCurrentImpression()
    {
        if ($this->getRule()) {
            return (int) $this->getRule()->getImpression();
        }

        return 0;
    }

    /**
     * Get Current Click
     *
     * @return  int
     */
    public function getCurrentClick()
    {
        if ($this->getRule()) {
            return (int) $this->getRule()->getClick();
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        $model = $this->registry->registry('autorelated_rule');
        if ($model && $model->getId()) {
            return false;
        }

        return true;
    }
}
