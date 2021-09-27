<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\ResourceModel\SalesOrder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection model
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = "id";

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

    protected function _construct()
    {
        $this->_init(
            \Webkul\MobikulCore\Model\SalesOrder::class,
            \Webkul\MobikulCore\Model\ResourceModel\SalesOrder::class
        );
        $this->_map["fields"]["id"] = "main_table.id";
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag("store_filter_added")) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    public function setOrderData($condition, $attributeData)
    {
        return $this->getConnection()->update(
            $this->getTable("mobikul_sales_order"),
            $attributeData,
            $where = $condition
        );
    }
}
