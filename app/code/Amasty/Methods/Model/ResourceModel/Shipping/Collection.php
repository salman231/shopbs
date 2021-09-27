<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Model\ResourceModel\Shipping;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Methods\Model\Shipping', 'Amasty\Methods\Model\ResourceModel\Shipping');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}