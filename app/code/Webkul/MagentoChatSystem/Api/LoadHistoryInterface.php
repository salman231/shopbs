<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Api;
 
interface LoadHistoryInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param int $currentPage
     * @param int $customerId
     * @return string.
     */
    public function loadHistory($currentPage, $customerId);
}
