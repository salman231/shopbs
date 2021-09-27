<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Model\ResourceModel\CommissionRules;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Webkul MpAdvancedCommission ResourceModel CommissionRules collection
 */
class Collection extends AbstractCollection
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface     $entityFactory
     * @param \Psr\Log\LoggerInterface                                      $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface  $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                     $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface                    $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null           $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null     $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->storeManager = $storeManager;
    }

    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MpAdvancedCommission\Model\CommissionRules::class,
            \Webkul\MpAdvancedCommission\Model\ResourceModel\CommissionRules::class
        );
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }
}
