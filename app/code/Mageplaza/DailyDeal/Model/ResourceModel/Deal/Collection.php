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

namespace Mageplaza\DailyDeal\Model\ResourceModel\Deal;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\DailyDeal\Model\Deal;
use Mageplaza\DailyDeal\Model\ResourceModel\Deal as ResourceDeal;

/**
 * Class Collection
 * @package Mageplaza\DailyDeal\Model\ResourceModel\Deal
 */
class Collection extends AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'deal_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_dailydeal_deal_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'deal_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Deal::class, ResourceDeal::class);
    }
}
