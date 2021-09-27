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
 
interface SaveAssignedChatInterface
{
    /**
     * Returns assigned response
     *
     * @api
     * @param int $customerId
     * @param string $uniqueId
     * @return string  agentassigned chat data.
     */
    public function assignChat($customerId, $uniqueId);

    /**
     * Returns assigned response
     *
     * @api
     * @param int $customerId
     * @param string $uniqueId
     * @param int $receiverId
     * @param string $receiverUniqueId
     * @return string  agentassigned chat data.
     */
    public function assignmentCheck($customerId, $uniqueId, $receiverId, $receiverUniqueId);
}
