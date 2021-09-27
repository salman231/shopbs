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
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Mageplaza\AutoRelated\Model\RuleFactory;

/**
 * Class BlockList
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Test
 */
class BlockList extends Extended
{
    /**
     * @var RuleFactory
     */
    protected $autoRelatedRuleFac;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param RuleFactory $autoRelatedRuleFac
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        RuleFactory $autoRelatedRuleFac,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);

        $this->autoRelatedRuleFac = $autoRelatedRuleFac;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('autorelated_test_block_list');
        $this->setUseAjax(false);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $ruleId     = $this->getRequest()->getParam('id');
        $collection = $this->autoRelatedRuleFac->create()->getCollection()
            ->addFieldToFilter(
                ['rule_id', 'parent_id'],
                [
                    ['eq' => $ruleId],
                    ['eq' => $ruleId]
                ]
            );
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn('rule_id', [
            'header' => __('ID'),
            'index'  => 'rule_id'
        ]);

        $this->addColumn('block_name', [
            'header'   => __('Variant'),
            'renderer' => 'Mageplaza\AutoRelated\Block\Adminhtml\Grid\Renderer\Name'
        ]);

        $this->addColumn('impression', [
            'header' => __('Impression'),
            'index'  => 'impression'
        ]);

        $this->addColumn('click', [
            'header' => __('Click'),
            'index'  => 'click'
        ]);

        $this->addColumn('ctr', [
            'header'   => __('CTR'),
            'renderer' => 'Mageplaza\AutoRelated\Block\Adminhtml\Grid\Renderer\Test\Ctr'
        ]);

        $this->addColumn('is_active', [
            'header'  => __('Status'),
            'index'   => 'is_active',
            'align'   => 'left',
            'type'    => 'options',
            'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
        ]);

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setSortable(false);
        $this->setEmptyText(__('There are no items.'));

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        $ruleId = $this->getRequest()->getParam('id');
        if ($row->getRuleId() == $ruleId) {
            return false;
        }

        return $this->getUrl('mparp/rule/edit', ['id' => $row->getRuleId(), 'type' => $row->getBlockType()]);
    }
}
