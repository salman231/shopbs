<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Model\ResourceModel\AgentRating;

use \Webkul\MagentoChatSystem\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MagentoChatSystem\Model\AgentRating::class,
            \Webkul\MagentoChatSystem\Model\ResourceModel\AgentRating::class
        );
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
