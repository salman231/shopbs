<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Model\ResourceModel\Order\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Webkul\DeliveryBoy\Model\ResourceModel\Order\Collection as OrderCollection;

class Collection extends OrderCollection implements SearchResultInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Api\Search\AggregationInterface
     */
    protected $_aggregations;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $mainTable
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        string $mainTable,
        string $eventPrefix,
        string $eventObject,
        string $resourceModel,
        string $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return self
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * @return null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return self
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * @param int $totalCount
     * @return self
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * @param array $items
     * @return self
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Join customer_entity sales_order and delieryboy_deliveryboy tables
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();
        $orderTable = $this->getTable('sales_order');
        $quoteTable = $this->getTable('quote');
        $this->getSelect()->join(
            $orderTable . ' as orderTable',
            'main_table.order_id = orderTable.entity_id',
            ['status' => 'orderTable.status','total' => 'orderTable.grand_total']
        );
        $this->getSelect()->joinLeft(
            $quoteTable . ' as quoteTable',
            'orderTable.quote_id = quoteTable.entity_id',
            [
                'firstname' => 'IFNULL(quoteTable.customer_firstname, "GUEST")',
                'lastname' => 'quoteTable.customer_lastname'
            ]
        );
        $this->getSelect()->joinLeft(
            $this->getTable('deliveryboy_deliveryboy') . ' as deliveryboyTable',
            'main_table.deliveryboy_id = deliveryboyTable.id',
            ['deliveryboy_name' => 'deliveryboyTable.name']
        );
        $this->_eventManager->dispatch(
            'wk_deliveryboy_assigned_order_collection_apply_filter_event',
            [
                'deliveryboy_order_collection' => $this,
                'collection_table_name' => 'main_table',
                'owner_id' => 0,
            ]
        );
    }
}
