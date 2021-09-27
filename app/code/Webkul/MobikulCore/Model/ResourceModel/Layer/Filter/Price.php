<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\ResourceModel\Layer\Filter;

/**
 * Class Price model
 */
class Price extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
{
    private $layer;
    private $session;
    private $storeManager;
    public $_customCollection;
    const MIN_POSSIBLE_PRICE = .01;
    protected $_eventManager = null;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->layer = $layerResolver->get();
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        parent::__construct($context, $eventManager, $layerResolver, $session, $storeManager, null);
    }

    protected function _getSelect()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $wholeData = $objectManager->create(\Magento\Framework\App\Request\Http::class)->getPostValue();
        if (isset($wholeData["custom"]) && $wholeData["customCollection"] == 1) {
            $procollection = $this->_customCollection;
        } else {
            $procollection = $this->layer->getProductCollection();
        }
        $procollection->addPriceData(
            $this->session->getCustomerGroupId(),
            $this->storeManager->getStore()->getWebsiteId()
        );
        if ($procollection->getCatalogPreparedSelect() !== null) {
            $selects = clone $procollection->getCatalogPreparedSelect();
        } else {
            $selects = clone $procollection->getSelect();
        }
        // reset columns, order and limitation conditions ///////////////////////////
        $selects->reset(\Magento\Framework\DB\Select::COLUMNS);
        $selects->reset(\Magento\Framework\DB\Select::ORDER);
        $selects->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $selects->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        // remove join with main table //////////////////////////////////////////////
        $fromPart = $selects->getPart(\Magento\Framework\DB\Select::FROM);
        if (!isset($fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS])
            || !isset($fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS])
        ) {
            return $selects;
        }
        // processing FROM part /////////////////////////////////////////////////////
        $priceIndexJoinPart = $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS];
        $priceIndexJoinCondition = explode('AND', $priceIndexJoinPart['joinCondition']);
        $priceIndexJoinPart['joinType'] = \Magento\Framework\DB\Select::FROM;
        $priceIndexJoinPart['joinCondition'] = null;
        $fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS] = $priceIndexJoinPart;
        unset($fromPart[\Magento\Catalog\Model\ResourceModel\Product\Collection::INDEX_TABLE_ALIAS]);
        $selects->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
        foreach ($fromPart as $key => $fromJoinItem) {
            $fromPart[$key]['joinCondition'] = $this->_replaceTableAlias($fromJoinItem['joinCondition']);
        }
        $selects->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
        // processing WHERE part ////////////////////////////////////////////////////
        $wherePart = $selects->getPart(\Magento\Framework\DB\Select::WHERE);
        foreach ($wherePart as $key => $wherePartItem) {
            $wherePart[$key] = $this->_replaceTableAlias($wherePartItem);
        }
        $selects->setPart(\Magento\Framework\DB\Select::WHERE, $wherePart);
        $excludedJoinPart = \Magento\Catalog\Model\ResourceModel\Product\Collection::MAIN_TABLE_ALIAS . '.entity_id';
        foreach ($priceIndexJoinCondition as $condition) {
            if (strpos($condition, $excludedJoinPart) !== false) {
                continue;
            }
            $selects->where($this->_replaceTableAlias($condition));
        }
        $selects->where($this->_getPriceExpression($selects) . ' IS NOT NULL');
        return $selects;
    }
}
