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

namespace Mageplaza\AutoRelated\Model\ResourceModel\Rule;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package Mageplaza\AutoRelated\Model\ResourceModel\Rule
 */
class Collection extends AbstractCollection
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param DateTime $date
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        DateTime $date,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->date = $date;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\AutoRelated\Model\Rule', 'Mageplaza\AutoRelated\Model\ResourceModel\Rule');
    }

    /**
     * @param $customerGroup
     * @param $storeId
     *
     * @return $this
     */
    public function addActiveFilter($customerGroup = null, $storeId = null)
    {
        $this->addFieldToFilter('is_active', true)
            ->setOrder('sort_order', Select::SQL_ASC);

        if (isset($customerGroup)) {
            $this->getSelect()
                ->join(
                    ['group' => $this->getTable('mageplaza_autorelated_block_rule_customer_group')],
                    'main_table.rule_id=group.rule_id',
                    ['customer_group_id' => 'group.customer_group_id']
                )
                ->where('customer_group_id = ?', $customerGroup);
        }

        if (isset($storeId)) {
            $this->getSelect()
                ->join(
                    ['store' => $this->getTable('mageplaza_autorelated_block_rule_store')],
                    'main_table.rule_id=store.rule_id',
                    ['store_id' => 'store.store_id']
                )
                ->where('store_id IN (?)', [0, $storeId])->group('rule_id');
        }

        return $this;
    }

    /**
     * @param $date
     *
     * @return $this
     */
    public function addDateFilter($date)
    {
        $this->getSelect()
            ->where('from_date is null OR from_date <= ?', $date)
            ->where('to_date is null OR to_date >= ?', $date);

        return $this;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function addTypeFilter($type)
    {
        $this->addFieldToFilter('block_type', $type);

        return $this;
    }

    /**
     * @param $mode
     *
     * @return $this
     */
    public function addModeFilter($mode)
    {
        $this->addFieldToFilter('display_mode', $mode);

        return $this;
    }

    /**
     * @param $location
     *
     * @return $this
     */
    public function addLocationFilter($location)
    {
        $this->addFieldToFilter('location', $location);

        return $this;
    }
}
