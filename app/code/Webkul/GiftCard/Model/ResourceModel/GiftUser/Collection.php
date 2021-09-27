<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Model\ResourceModel\GiftUser;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Webkul GiftCard ResourceModel Seller collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'giftuserid';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\GiftCard\Model\GiftUser::class,
            \Webkul\GiftCard\Model\ResourceModel\GiftUser::class
        );
        $this->_map['fields']['giftuserid'] = 'main_table.giftuserid';
        $this->_map['fields']['created_at'] = 'main_table.created_at';
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
