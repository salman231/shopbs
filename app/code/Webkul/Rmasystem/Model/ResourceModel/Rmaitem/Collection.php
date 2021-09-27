<?php
namespace Webkul\Rmasystem\Model\ResourceModel\Rmaitem;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\Rmaitem::class, \Webkul\Rmasystem\Model\ResourceModel\Rmaitem::class);
    }
}
