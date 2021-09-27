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
 
interface SaveCustomerInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $email Users name.
     * @param int $agentId Agent Id.
     * @param string $agentUniqueId Users name.
     * @return string Greeting message with users name.
     */
    public function save($message, $agentId, $agentUniqueId);
}
