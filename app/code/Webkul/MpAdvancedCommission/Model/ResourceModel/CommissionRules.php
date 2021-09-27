<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Model\ResourceModel;
 
class CommissionRules extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store model
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('marketplace_advanced_commision_rules', 'rule_id');
    }

    /**
     * Load an object using 'rule_id' field
     * if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(
        \Magento\Framework\Model\AbstractModel $object,
        $value,
        $field = null
    ) {
        if (!is_numeric($value) && ($field === null)) {
            $field = 'rule_id';
        }

        return parent::load($object, $value, $field);
    }
}
