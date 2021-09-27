<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\Queue\ResourceModel;

use Amasty\PageSpeedOptimizer\Api\Data\QueueInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Queue Collection
 *
 * @package Amasty\PageSpeedOptimizer
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\PageSpeedOptimizer\Model\Queue\Queue::class,
            \Amasty\PageSpeedOptimizer\Model\Queue\ResourceModel\Queue::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
        $this->setOrder(QueueInterface::QUEUE_ID, self::SORT_ORDER_ASC);
    }
}
