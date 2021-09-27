<?php
/**
 * Productkeys Productkeys ResourceModel.
 * @category    Dart
 *
 */
namespace Dart\Productkeys\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Productkeys extends AbstractDb
{
    /**
     * Construct.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime       $date
     * @param string|null                                       $resourcePrefix
     */
    public function __construct(
        Context $context,
        DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('dart_productkeys', 'id');
    }
}
