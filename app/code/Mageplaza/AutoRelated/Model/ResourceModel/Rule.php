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

namespace Mageplaza\AutoRelated\Model\ResourceModel;

use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\AutoRelated\Helper\Data;
use Mageplaza\AutoRelated\Model\Config\Source\DisplayMode;
use Mageplaza\AutoRelated\Model\RuleFactory;
use Zend_Db_Expr;

/**
 * Class Rule
 * @package Mageplaza\AutoRelated\Model\ResourceModel
 */
class Rule extends AbstractDb
{
    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @var RuleFactory
     */
    protected $autoRelatedRuleFac;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Rule constructor.
     *
     * @param DateTime $date
     * @param Context $context
     * @param RuleFactory $autoRelatedRuleFac
     * @param Cart $cart
     * @param ProductFactory $productFactory
     * @param Data $helperData
     */
    public function __construct(
        DateTime $date,
        Context $context,
        RuleFactory $autoRelatedRuleFac,
        Cart $cart,
        ProductFactory $productFactory,
        Data $helperData
    ) {
        parent::__construct($context);

        $this->autoRelatedRuleFac = $autoRelatedRuleFac;
        $this->cart               = $cart;
        $this->productFactory     = $productFactory;
        $this->date               = $date;
        $this->helperData         = $helperData;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mageplaza_autorelated_block_rule', 'rule_id');
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        if (is_array($object->getData('display_additional'))) {
            $object->setData('display_additional', implode(',', $object->getData('display_additional')));
        }

        if (is_array($object->getData('add_ruc_product'))) {
            $object->setData('add_ruc_product', implode(',', $object->getData('add_ruc_product')));
        }

        if (is_array($object->getData('product_not_displayed'))) {
            $object->setData('product_not_displayed', implode(',', $object->getData('product_not_displayed')));
        }

        if ($object->getData('location') === 'custom') {
            $object->setData('display_mode', DisplayMode::TYPE_BLOCK);
        }

        return parent::_beforeSave($object);
    }

    /**
     * customer group rule config
     *
     * @param string $ruleId
     *
     * @return array
     */
    public function getCustomerGroupByRuleId($ruleId)
    {
        $tableName = $this->getTable('mageplaza_autorelated_block_rule_customer_group');
        $select    = $this->getConnection()->select()
            ->from($tableName, 'customer_group_id')
            ->where('rule_id = ?', $ruleId);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * store view rule config
     *
     * @param string $ruleId
     *
     * @return array
     */
    public function getStoresByRuleId($ruleId)
    {
        $tableName = $this->getTable('mageplaza_autorelated_block_rule_store');
        $select    = $this->getConnection()->select()
            ->from($tableName, 'store_id')
            ->where('rule_id = ?', $ruleId);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * delete store view and customer rule
     *
     * @param string $ruleId
     *
     * @return void
     */
    public function deleteOldData($ruleId)
    {
        if ($ruleId) {
            $where = ['rule_id = ?' => $ruleId];
            $this->deleteMultipleData('mageplaza_autorelated_block_rule_store', $where);
            $this->deleteMultipleData('mageplaza_autorelated_block_rule_customer_group', $where);
        }
    }

    /**
     * update store view
     *
     * @param array $data
     * @param string $ruleId
     *
     * @return void
     */
    public function updateStore($data, $ruleId)
    {
        $dataInsert = [];
        foreach ($data as $storeId) {
            $dataInsert[] = [
                'rule_id'  => $ruleId,
                'store_id' => $storeId
            ];
        }
        $this->updateMultipleData('mageplaza_autorelated_block_rule_store', $dataInsert);
    }

    /**
     * update customer group
     *
     * @param array $data
     * @param string $ruleId
     *
     * @return void
     */
    public function updateCustomerGroup($data, $ruleId)
    {
        $dataInsert = [];
        foreach ($data as $customerGroupId) {
            $dataInsert[] = [
                'rule_id'           => $ruleId,
                'customer_group_id' => $customerGroupId
            ];
        }
        $this->updateMultipleData('mageplaza_autorelated_block_rule_customer_group', $dataInsert);
    }

    /**
     * update database
     *
     * @param string $tableName
     * @param array $data
     *
     * @return void
     */
    public function updateMultipleData($tableName, $data = [])
    {
        $table = $this->getTable($tableName);
        if ($table && !empty($data)) {
            $this->getConnection()->insertMultiple($table, $data);
        }
    }

    /**
     * delete database
     *
     * @param string $tableName
     * @param array $where
     *
     * @return void
     */
    public function deleteMultipleData($tableName, $where = [])
    {
        $table = $this->getTable($tableName);
        if ($table && !empty($where)) {
            $this->getConnection()->delete($table, $where);
        }
    }

    /**
     * @param $ruleId
     */
    public function deleteActionIndex($ruleId)
    {
        $this->deleteMultipleData('mageplaza_autorelated_actions_index', ['rule_id = ?' => $ruleId]);
    }

    /**
     * @param $data
     */
    public function insertActionIndex($data)
    {
        $this->updateMultipleData('mageplaza_autorelated_actions_index', $data);
    }

    /**
     * get products by rule
     *
     * @param string $ruleId
     * @param string $productId
     *
     * @return array
     */
    public function getProductListByRuleId($ruleId, $productId = null)
    {
        $adapter    = $this->getConnection();
        $indexTable = $this->getTable('mageplaza_autorelated_actions_index');
        $select     = $adapter->select()
            ->from($indexTable, 'product_id')
            ->where('rule_id = ?', $ruleId)
            ->where('product_id != ?', $productId);

        return $adapter->fetchCol($select);
    }

    /**
     * get rule by id
     *
     * @param $ruleId
     * @param string $field
     *
     * @return array
     * @throws LocalizedException
     */
    public function getRuleData($ruleId, $field = 'rule_id')
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable())
            ->where($field . ' = ?', $ruleId);

        return $adapter->fetchRow($select);
    }

    /**
     * update impression rule by id
     *
     * @param $ruleId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function updateImpression($ruleId)
    {
        $rule = $this->getRuleData($ruleId);
        if (empty($rule) || !isset($rule['is_active']) || !$rule['is_active']) {
            return $this;
        }

        if (isset($rule['parent_id']) && $rule['parent_id']) {
            $bind  = ['total_impression' => new Zend_Db_Expr('total_impression+1')];
            $where = ['rule_id = ?' => (int) $rule['parent_id']];

            $this->getConnection()->update($this->getMainTable(), $bind, $where);
        }

        $bind  = [
            'impression'       => new Zend_Db_Expr('impression+1'),
            'total_impression' => new Zend_Db_Expr('total_impression+1')
        ];
        $where = ['rule_id = ?' => (int) $ruleId];

        $this->getConnection()->update($this->getMainTable(), $bind, $where);

        return $this;
    }

    /**
     * update click rule by id
     *
     * @param $ruleId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function updateClick($ruleId)
    {
        $rule = $this->getRuleData($ruleId);
        if (empty($rule) || !isset($rule['is_active']) || !$rule['is_active']) {
            return $this;
        }

        if (isset($rule['parent_id']) && $rule['parent_id']) {
            $bind  = ['total_click' => new Zend_Db_Expr('total_click+1')];
            $where = ['rule_id = ?' => (int) $rule['parent_id']];

            $this->getConnection()->update($this->getMainTable(), $bind, $where);
        }

        $bind  = [
            'click'       => new Zend_Db_Expr('click+1'),
            'total_click' => new Zend_Db_Expr('total_click+1')
        ];
        $where = ['rule_id = ?' => (int) $ruleId];

        $this->getConnection()->update($this->getMainTable(), $bind, $where);

        return $this;
    }
}
