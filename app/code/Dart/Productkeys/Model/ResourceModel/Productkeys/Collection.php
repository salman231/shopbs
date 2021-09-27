<?php
/**
 * Productkeys Productkeys Collection.
 * @category    Dart
 *
 */
namespace Dart\Productkeys\Model\ResourceModel\Productkeys;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Dart\Productkeys\Model\Productkeys', 'Dart\Productkeys\Model\ResourceModel\Productkeys');
    }
}
