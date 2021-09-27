<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Queue\ResourceModel;

use Amasty\PageSpeedOptimizer\Api\Data\QueueInterface;
use Amasty\PageSpeedOptimizer\Setup\Operation\CreateQueueTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Queue Resource Model
 *
 * @package Amasty\PageSpeedOptimizer
 */
class Queue extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(CreateQueueTable::TABLE_NAME, QueueInterface::QUEUE_ID);
    }

    public function clear()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }

    public function deleteByIds($ids = [])
    {
        $this->getConnection()->delete($this->getMainTable(), [QueueInterface::QUEUE_ID . ' in (?) ' => $ids]);
    }
}
