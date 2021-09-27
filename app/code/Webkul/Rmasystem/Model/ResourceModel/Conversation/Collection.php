<?php
namespace Webkul\Rmasystem\Model\ResourceModel\Conversation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\Conversation::class, \Webkul\Rmasystem\Model\ResourceModel\Conversation::class);
    }
}
