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
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions;
use Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Conditions\ProductCart as ProductConditions;
use Mageplaza\AutoRelated\Helper\Data;
use Mageplaza\AutoRelated\Model\RuleFactory;
use Zend_Serializer_Exception;

/**
 * Class Category
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule\Edit\Tab\Conditions
 */
class Category extends ProductConditions
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Conditions $conditions
     * @param Fieldset $rendererFieldset
     * @param RuleFactory $autoRelatedRuleFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Conditions $conditions,
        Fieldset $rendererFieldset,
        RuleFactory $autoRelatedRuleFactory,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $conditions,
            $rendererFieldset,
            $autoRelatedRuleFactory,
            $data
        );
    }

    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'rule/category/conditions.phtml';

    /**
     * @return array|mixed
     * @throws Zend_Serializer_Exception
     */
    public function getCategoryIds()
    {
        $ids        = [];
        $conditions = $this->registry->registry('autorelated_rule_category');
        if ($conditions) {
            $ids = $this->helperData->unserialize($conditions);
        }

        return $ids;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     * @throws Zend_Serializer_Exception
     */
    public function getCategoryTree()
    {
        $ids   = $this->getCategoryIds();
        $block = $this->getLayout()->createBlock(
            'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree',
            'autorelated_rule_widget_chooser_category_ids',
            ['data' => ['js_form_object' => 'autorealated_rule_form']]
        )->setCategoryIds(
            $ids
        );

        return $block->toHtml();
    }
}
