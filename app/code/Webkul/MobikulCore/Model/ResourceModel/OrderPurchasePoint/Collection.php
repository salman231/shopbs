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
namespace Webkul\MobikulCore\Model\ResourceModel\OrderPurchasePoint;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'mobikul_orderpurchasepoint';
    protected $_eventObject = "mobikul_orderpurchasepoint";

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
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MobikulCore\Model\OrderPurchasePoint::class,
            \Webkul\MobikulCore\Model\ResourceModel\OrderPurchasePoint::class
        );
        $this->_map["fields"]["entity_id"] = "main_table.entity_id";
    }
}
