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
namespace Webkul\MagentoChatSystem\Api\Data;

/**
 * MagentoChatSystem assigned chat interface.
 * @api
 */
interface TotalAssignedChatInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID             = 'entity_id';
    const AGENT_ID              = 'agent_id';
    const AGENT_UNIQUE_ID       = 'agent_unique_id';
    const TOTAL_ACTIVE_CHAT     = 'total_active_chat';

    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get agent ID
     *
     * @return int|null
     */
    public function getAgentId();

    /**
     * Get agent unique ID
     *
     * @return string|null
     */
    public function getAgentUniqueId();

    /**
     * Get total active chat
     *
     * @return int|null
     */
    public function getTotalActiveChat();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\TotalAssignedChatInterface
     */
    public function setId($id);

    /**
     * Set agent ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\TotalAssignedChatInterface
     */
    public function setAgentId($agentId);

    /**
     * Set agent unique ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\TotalAssignedChatInterface
     */
    public function setAgentUniqueId($uniqueId);

     /**
      * Set total active chat ID
      *
      * @param int $id
      * @return \Webkul\MagentoChatSystem\Api\Data\TotalAssignedChatInterface
      */
    public function setTotalActiveChat($activeChat);
}
