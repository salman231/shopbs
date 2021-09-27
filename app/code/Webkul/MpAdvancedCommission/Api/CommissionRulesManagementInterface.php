<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Api;

/**
 * @api
 */
interface CommissionRulesManagementInterface
{
    /**
     * Retrieve commission rules counts.
     *
     * @return Webkul\MpAdvancedCommission\Api\Data\CommissionRulesInterface[]
     */
    public function getRulesCount();
}
