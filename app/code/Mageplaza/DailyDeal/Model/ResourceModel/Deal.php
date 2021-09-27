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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Model\ResourceModel;

use DateTimeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Expr;

/**
 * Class Deal
 * @package Mageplaza\DailyDeal\Model\ResourceModel
 */
class Deal extends AbstractDb
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $_date;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Deal constructor.
     *
     * @param Context $context
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->_date         = $date;
        $this->_storeManager = $storeManager;

        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_dailydeal_deal', 'deal_id');
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this|AbstractDb
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** save store Ids */
        if (is_array($object->getStoreIds())) {
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }

        return $this;
    }

    /**
     * Update Sale Qty of running deal in database
     *
     * @param $productId
     * @param $qty
     *
     * @throws LocalizedException
     */
    public function updateRunningSaleQty($productId, $qty)
    {
        $adapter = $this->getConnection();
        $storeId = $this->_storeManager->getStore()->getId();

        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('product_id = ?', $productId)
            ->where('store_ids = 0 OR store_ids IN (?)', (int) $storeId)
            ->where('status = ?', 1)
            ->where('deal_qty > sale_qty')
            ->where('date_from <= ?', $this->_date->date())
            ->where('date_to >= ?', $this->_date->date());
        $dealIds = $adapter->fetchCol($select);
        if ($dealIds) {
            foreach ($dealIds as $dealId) {
                $bind  = [
                    'sale_qty' => new Zend_Db_Expr('sale_qty+' . (int) $qty)
                ];
                $where = [
                    'deal_id = ?' => (int) $dealId,
                ];
                $this->getConnection()->update($this->getMainTable(), $bind, $where);
            }
        }
    }

    /**
     * Update Sale qty
     *
     * @param $productId
     * @param $qty
     *
     * @throws LocalizedException
     */
    public function updateSaleQty($productId, $qty)
    {
        $adapter = $this->getConnection();
        $storeId = $this->_storeManager->getStore()->getId();

        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where('product_id = ?', $productId)
            ->where('store_ids = 0 OR store_ids IN (?)', (int) $storeId);
        $dealIds = $adapter->fetchCol($select);
        if ($dealIds) {
            foreach ($dealIds as $dealId) {
                $bind  = ['sale_qty' => new Zend_Db_Expr('sale_qty+' . (int) $qty)];
                $where = ['deal_id = ?' => (int) $dealId];
                $this->getConnection()->update($this->getMainTable(), $bind, $where);
            }
        }
    }

    /**
     * Retrieve product prices by daily deal for specific date, store
     * Collect data with  product Id => price pairs
     *
     * @param DateTimeInterface $date
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     */
    public function getDealPrices(DateTimeInterface $date, $storeId, $productIds)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from($this->getTable('mageplaza_dailydeal_deal'), ['product_id', 'deal_price'])
            ->where('date_from <= ?', $date->format('Y-m-d H:i:s'))
            ->where('date_to >= ?', $date->format('Y-m-d H:i:s'))
            ->where('FIND_IN_SET( ? , store_ids)', $storeId)
            ->where('status = ?', 1)
            ->where('product_id IN(?)', $productIds)
            ->where('deal_qty > sale_qty')
            ->where('date_from < date_to');

        return $connection->fetchPairs($select);
    }
}
