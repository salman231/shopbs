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

namespace Mageplaza\AutoRelated\Model\ResourceModel\Rule\Grid;

/**
 * Class Collection
 * @package Mageplaza\AutoRelated\Model\ResourceModel\Rule\Grid
 */
class Collection extends \Mageplaza\AutoRelated\Model\ResourceModel\Rule\Collection
{
    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFieldToFilter('parent_id', ['eq' => 0]);

        return $this;
    }
}
