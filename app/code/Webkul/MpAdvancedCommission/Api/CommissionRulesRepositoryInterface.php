<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Api;

use Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface;

/**
 * @api
 */
interface CommissionRulesRepositoryInterface
{
    /**
     * Get info about commission rule by rule id.
     *
     * @param int $ruleId
     *
     * @return CommissionRulesInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($ruleId);

    /**
     * Delete commission rule.
     *
     * @param CommissionRulesInterface $commissionRules
     *
     * @return bool Will returned True if deleted
     *
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(CommissionRulesInterface $commissionRules);

    /**
     * @param int $ruleId
     *
     * @return bool Will returned True if deleted
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($ruleId);

    /**
     * Get Commission Rules list.
     *
     * @return CommissionRulesInterface
     */
    public function getList();

    /**
     * Get info about commission rule by commission type.
     *
     * @param int $type
     *
     * @return CommissionRulesInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCommissionType($type);
}
