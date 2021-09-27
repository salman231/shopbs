<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Model\ResourceModel;

/**
 * Abstract collection of chat system model
 */
abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $loggerInterface
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * performAfterLoad method for performing operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);
        if (!empty($items)) {
            $connection = $this->getConnection();
        }
    }

    /**
     * Add field filter to collection
     *
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Get total count sql
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

        return $countSelect;
    }
}
