<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Model;

class Shipping extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init('Amasty\Methods\Model\ResourceModel\Shipping');
    }
}