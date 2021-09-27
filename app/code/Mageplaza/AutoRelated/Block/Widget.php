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

namespace Mageplaza\AutoRelated\Block;

use Mageplaza\AutoRelated\Block\Product\Block;
use Mageplaza\AutoRelated\Model\Rule;

/**
 * Class ProductList
 * @package Mageplaza\AutoRelated\Block\Widget
 */
class Widget extends Block
{
    /**
     * @inheritdoc
     */
    public function getProductCollection()
    {
        $ruleId = $this->getData('rule_id');
        if (!$this->helper->isEnableArpBlock() || !$ruleId) {
            return [];
        }

        $type = $this->helper->getData('type');
        if (!$type || (in_array($type, ['product', 'category']) && !$this->helper->getData('entity_id'))) {
            return [];
        }

        $rules = $this->helper->getCustomRules();
        /** @var Rule $rule */
        foreach ($rules as $rule) {
            if ($rule->getData('location') != 'custom') {
                continue;
            }

            if ($rule->getId() == $ruleId) {
                $this->rule = $rule;
                break;
            }
        }

        return parent::getProductCollection();
    }
}
