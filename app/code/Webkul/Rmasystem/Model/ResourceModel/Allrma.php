<?php
namespace Webkul\Rmasystem\Model\ResourceModel;

class Allrma extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('wk_rma', 'rma_id');
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'rma_id';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }
}
