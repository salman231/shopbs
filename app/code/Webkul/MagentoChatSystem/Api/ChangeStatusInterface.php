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

interface ChangeStatusInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param int $status
     * @return string.
     */
    public function changeStatus($status);
}
