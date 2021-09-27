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

namespace Mageplaza\AutoRelated\Block\Adminhtml\Rule;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Mageplaza\AutoRelated\Helper\Data;

/**
 * Class Edit
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Rule
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->urlBuilder    = $context->getUrlBuilder();

        parent::__construct($context, $data);
    }

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Apply" button
     * Add "Save and Continue" button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'Mageplaza_AutoRelated';
        $this->_controller = 'adminhtml_rule';

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

        if ($this->getRuleId() && !$this->isTesting()) {
            $this->buttonList->add(
                'add_testing',
                [
                    'class'    => 'save',
                    'label'    => __('Add A/B Testing'),
                    'on_click' => sprintf("location.href = '%s';", $this->getAddTestUrl()),
                ],
                10
            );
        }

        if ($this->getRuleId() && $this->getParent()) {
            $this->buttonList->add(
                'back_parent',
                [
                    'label'    => __('Parent'),
                    'on_click' => sprintf("location.href = '%s';", $this->getBackParentUrl()),
                ],
                10
            );
        }

        $this->_formScripts[] = "
    require(['jquery'], function($){
        var element = $('#general_rule_location'),
            displayModeEl = $('#block_config_rule_display_mode'),
            blockLayoutEl = $('#block_config_rule_product_layout'),
            additionEl = $('#block_config_rule_limit_number, #block_config_rule_display_additional'),
            widgetGuide = " . $this->getLocationNote() . ";
        
        changeNote(element.val());
        
        element.change(function () {
            changeNote($(this).val());
        });
        
        function changeNote(option) {
            var optionNote = widgetGuide.hasOwnProperty(option) ? widgetGuide[option] : widgetGuide['default'];
            $('#location-note').html(optionNote);
            
            if(option === 'custom'){
                displayModeEl.val(1);
                displayModeEl.prop('disabled', true);
            } else {
                displayModeEl.prop('disabled', false);
            }
            
            if(option === 'left-popup-content' || option === 'right-popup-content'){
                blockLayoutEl.val(1);
                blockLayoutEl.prop('disabled', true);
                additionEl.prop('disabled', true);
            } else {
                blockLayoutEl.prop('disabled', false);
                additionEl.prop('disabled', false);
            }
        }
    })";
    }

    /**
     * Get Html of Widget Guide
     *
     * @return string
     */
    public function getLocationNote()
    {
        $model  = $this->_coreRegistry->registry('autorelated_rule');
        $ruleId = $model->getId() ?: '1';

        $customHtml = <<<HTML
<h3>How to use</h3>
<ul class="arp-location-display">
    <li>
        <span>Add Widget with name "ARP Product List" and set "Rule Id" for it.</span>
        </li>
    <li>
        <span>CMS Page, CMS Static Block</span>
        <code>{{block class="Mageplaza\AutoRelated\Block\Widget" rule_id="{$ruleId}"}}</code>
        <p>You can paste the above block of snippet into Product page, Category page in Magento 2 and set RuleId for it.</p>
    </li>
    <li>
        <span>Template .phtml file</span>
        <code>{$this->_escaper->escapeHtml('<?php echo $block->getLayout()->createBlock(\Mageplaza\AutoRelated\Block\Widget::class)->setRuleId(' . $ruleId . ')->toHtml();?>')}</code>
        <p>Open a .phtml file and insert where you want to display ARP product list.</p>
    </li>
</ul>
HTML;
        $noteGuide  = [
            'default'             => 'Select the position to display block.',
            'left-popup-content'  => __('The popup can be shown on the bottom left of the page depend on priority of rules. Only first product will be shown.'),
            'right-popup-content' => __('The popup can be shown on the bottom right of the page depend on priority of rules. Only first product will be shown.'),
            'custom'              => $customHtml,
        ];

        return Data::jsonEncode($noteGuide);
    }

    /**
     * Get URL for back parent button
     *
     * @return string
     */
    public function getBackParentUrl()
    {
        $parentId = $this->getParent();
        $type     = $this->getBlockType();

        return $this->urlBuilder->getUrl('*/*/edit', ['id' => $parentId, 'type' => $type]);
    }

    /**
     * Getter for form header text
     *
     * @return Phrase
     */
    public function getHeaderText()
    {
        $rule = $this->getRule();
        if ($rule->getRuleId()) {
            return __("Edit Rule '%1'", $this->escapeHtml($rule->getName()));
        } else {
            return __('New Rule');
        }
    }

    /**
     * Get URL for back button
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->_coreRegistry->registry('autorelated_test_add')) {
            $ruleId = $this->getRuleId();
            $type   = $this->getBlockType();

            return $this->urlBuilder->getUrl('*/*/edit', ['id' => $ruleId, 'type' => $type]);
        }

        return $this->urlBuilder->getUrl('*/*/');
    }

    /**
     * Check Rule
     *
     * @return boolean
     */
    private function isTesting()
    {
        $testAdd         = $this->_coreRegistry->registry('autorelated_test_add');
        $autoRelatedRule = $this->getRule();
        if (!$autoRelatedRule) {
            return false;
        }
        if ($testAdd || $autoRelatedRule->getParentId() || $autoRelatedRule->hasChild()) {
            return true;
        }

        return false;
    }

    /**
     * Get Url Add A/B Testing
     *
     * @return string
     */
    private function getAddTestUrl()
    {
        $rule = $this->getRule();
        if ($rule) {
            return $this->urlBuilder->getUrl(
                'mparp/rule/test',
                [
                    'id'   => $rule->getRuleId(),
                    'type' => $this->getBlockType()
                ]
            );
        }

        return $this->urlBuilder->getUrl();
    }

    /**
     * Return the current Rule Id.
     *
     * @return int|null
     */
    private function getRuleId()
    {
        $autoRelatedRule = $this->getRule();

        return $autoRelatedRule ? $autoRelatedRule->getId() : null;
    }

    /**
     * Return the block type Rule.
     *
     * @return string|null
     */
    private function getBlockType()
    {
        $autoRelatedRule = $this->getRule();

        return $autoRelatedRule ? $autoRelatedRule->getBlockType() : null;
    }

    /**
     *
     * @return object
     */
    private function getRule()
    {
        return $this->_coreRegistry->registry('autorelated_rule');
    }

    /**
     *
     * @return int
     */
    private function getParent()
    {
        if ($this->getRule()) {
            return (int) $this->getRule()->getParentId();
        }

        return 0;
    }
}
