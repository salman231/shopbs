<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Payment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_methods_payment', 'entity_id');
    }
}