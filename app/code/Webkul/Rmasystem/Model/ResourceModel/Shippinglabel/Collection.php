<?php
namespace Webkul\Rmasystem\Model\ResourceModel\Shippinglabel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\Shippinglabel::class, \Webkul\Rmasystem\Model\ResourceModel\Shippinglabel::class);
        $this->_map['fields']['id'] = 'main_table.id';
    }
}
