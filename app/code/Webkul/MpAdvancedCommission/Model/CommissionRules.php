<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Model;

use Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * CommissionRules Model.
 *
 * @method ResourceModel\CommissionRules _getResource()
 * @method ResourceModel\CommissionRules getResource()
 */
class CommissionRules extends \Magento\Framework\Model\AbstractModel implements
    CommissionRulesInterface,
    IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_RULE_ID = 'no-route';

    /**
     * commission type.
     */
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    /**
     * Marketplace CommissionRules cache tag.
     */
    const CACHE_TAG = 'marketplace_advanced_commision_rules';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_advanced_commision_rules';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_advanced_commision_rules';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpAdvancedCommission\Model\ResourceModel\CommissionRules::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteCommissionRules();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route CommissionRules.
     *
     * @return \Webkul\MpAdvancedCommission\Model\CommissionRules
     */
    public function noRouteCommissionRules()
    {
        return $this->load(self::NOROUTE_RULE_ID, $this->getIdFieldName());
    }

    /**
     * Prepare group's statuses.
     * Available event mpsellergroup_sellergroup_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableTypes()
    {
        return [self::TYPE_FIXED => __('Fixed'), self::TYPE_PERCENT => __('Percent')];
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::RULE_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface
     */
    public function setId($id)
    {
        return $this->setData(self::RULE_ID, $id);
    }

    /**
     * Get Price From.
     *
     * @return int|null
     */
    public function getPriceFrom()
    {
        return $this->_getData(self::PRICE_FROM);
    }

    /**
     * Set Price From.
     *
     * @param int $priceFrom
     *
     * @return \Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface
     */
    public function setPriceFrom($priceFrom)
    {
        return $this->setData(self::PRICE_FROM, $priceFrom);
    }

    /**
     * Get Price To.
     *
     * @return int|null
     */
    public function getPriceTo()
    {
        return $this->_getData(self::PRICE_TO);
    }

    /**
     * Set Price To.
     *
     * @param int $priceTo
     *
     * @return \Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface
     */
    public function setPriceTo($priceTo)
    {
        return $this->setData(self::PRICE_TO, $priceTo);
    }

    /**
     * Get Commission Type.
     *
     * @return int
     */
    public function getCommissionType()
    {
        $commissionType = $this->_getData(self::COMMISSION_TYPE);

        return $commissionType !== null ? $commissionType : self::TYPE_FIXED;
    }

    /**
     * Get Commission Type.
     *
     * @return int
     */
    public function setCommissionType($commissionType)
    {
        $commissionType = $this->_getData(self::COMMISSION_TYPE);

        return $commissionType !== null ? $commissionType : self::TYPE_PERCENT;
    }

    /**
     * Get Commission Amount.
     *
     * @return int|null
     */
    public function getAmount()
    {
        return $this->_getData(self::AMOUNT);
    }

    /**
     * Set Commission Amount.
     *
     * @param int $amount
     *
     * @return \Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface
     */
    public function setAmount($amount)
    {
        return $this->_getData(self::AMOUNT, $amount);
    }

    /**
     * Get product creation date.
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * Set product created date.
     *
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get previous product update date.
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_getData(self::UPDATED_AT);
    }

    /**
     * Set product updated date.
     *
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
