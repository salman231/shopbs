<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Api\Data;

/**
 * MpAdvancedCommission CommissionRules interface.
 *
 * @api
 */
interface CommissionRulesInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RULE_ID = 'rule_id';

    const PRICE_FROM = 'price_from';

    const PRICE_TO = 'price_to';

    const COMMISSION_TYPE = 'commission_type';

    const AMOUNT = 'amount';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    /**#@-*/

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface
     */
    public function setId($id);

    /**
     * Get Price From.
     *
     * @return int|null
     */
    public function getPriceFrom();

    /**
     * Set Price From.
     *
     * @param int $priceFrom
     *
     * @return \Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface
     */
    public function setPriceFrom($priceFrom);

    /**
     * Get Price To.
     *
     * @return int|null
     */
    public function getPriceTo();

    /**
     * Set Price To.
     *
     * @param int $priceTo
     *
     * @return \Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface
     */
    public function setPriceTo($priceTo);

    /**
     * Get Commission Type.
     *
     * @return int
     */
    public function getCommissionType();

    /**
     * Set Commission Type.
     *
     * @return int
     */
    public function setCommissionType($commissionType);

    /**
     * Get Commission Amount.
     *
     * @return int|null
     */
    public function getAmount();

    /**
     * Set Commission Amount.
     *
     * @param int $amount
     *
     * @return \Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface
     */
    public function setAmount($amount);

    /**
     * Product created date.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set product created date.
     *
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Product updated date.
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set product updated date.
     *
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
