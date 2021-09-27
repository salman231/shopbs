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

namespace Mageplaza\AutoRelated\Block\Adminhtml\Grid;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Container;
use Mageplaza\AutoRelated\Helper\Data;
use Mageplaza\AutoRelated\Model\Config\Source\Type as RuleType;

/**
 * Class Rule
 * @package Mageplaza\AutoRelated\Block\Adminhtml\Grid
 */
class Rule extends Container
{
    /**
     * @var Data
     */
    protected $autoRealatedType;

    /**
     * Constructor
     *
     * @param Context $context
     * @param RuleType $autoRealatedType
     * @param array $data
     */
    public function __construct(
        Context $context,
        RuleType $autoRealatedType,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->autoRealatedType = $autoRealatedType;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->removeButton('add');
    }

    /**
     * Prepare button and grid
     *
     * @return Container
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id'           => 'add_new_rule_block',
            'label'        => __('Add Rule'),
            'class'        => 'add',
            'button_class' => '',
            'class_name'   => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options'      => $this->_getAddRuleButtonOptions(),
        ];
        $this->buttonList->add('add_new_rule_block', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add' split button
     *
     * @return array
     */
    protected function _getAddRuleButtonOptions()
    {
        $splitButtonOptions = [];
        $types              = $this->autoRealatedType->getPageType();
        foreach ($types as $typeId => $typeLabel) {
            $splitButtonOptions[$typeId] = [
                'label'   => $typeLabel,
                'onclick' => "setLocation('" . $this->_getRuleCreateUrl($typeId) . "')",
                'default' => RuleType::DEFAULT_TYPE_PAGE === $typeId,
            ];
        }

        return $splitButtonOptions;
    }

    /**
     * Retrieve rule create url by specified block type
     *
     * @param string $type
     *
     * @return string
     */
    protected function _getRuleCreateUrl($type)
    {
        return $this->getUrl('mparp/*/new', ['type' => $type]);
    }
}
