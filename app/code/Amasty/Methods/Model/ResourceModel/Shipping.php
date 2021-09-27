<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Shipping extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_methods_shipping', 'entity_id');
    }
}