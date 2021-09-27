<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Customform
 */

namespace Amasty\Customform\Model\ResourceModel\Form\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Zend_Db_Expr;
use Amasty\Customform\Model\Config\Source\Status;

class Collection extends \Amasty\Customform\Model\ResourceModel\Form\Collection implements SearchResultInterface
{
    protected $aggregations;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->addFilterToMap('form_id', 'main_table.form_id');
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    public function getTotalCount()
    {
        return $this->getSize();
    }

    public function setTotalCount($totalCount)
    {
        return $this;
    }

    public function setItems(array $items = null)
    {
        return $this;
    }


    /**
     * Create all ids retrieving select with limitation
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $idsSelect->limit($limit, $offset);

        return $idsSelect;
    }

    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        $statusExpression = 'sum(case when answers.admin_response_status = \'%s\' then 1 else 0 end)';

        $this->getSelect()
            ->joinLeft(
                ['answers' => $this->getTable('am_customform_answer')],
                'main_table.form_id=answers.form_id',
                'form_id as formId'
            )
            ->columns('COUNT(answers.form_id) as answers_count')
            ->columns(['answered_count' => new Zend_Db_Expr(sprintf($statusExpression, Status::ANSWERED))])
            ->columns(['pending_count' => new Zend_Db_Expr(sprintf($statusExpression, Status::PENDING))])
            ->group('main_table.form_id');

        return parent::_beforeLoad();
    }
}
