<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel;

use Amasty\PageSpeedOptimizer\Setup\Operation\CreateBundleTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Bundle Resource Model
 *
 * @package Amasty\PageSpeedOptimizer
 */
class Bundle extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(CreateBundleTable::TABLE_NAME, 'filename_id');
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function clear()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
