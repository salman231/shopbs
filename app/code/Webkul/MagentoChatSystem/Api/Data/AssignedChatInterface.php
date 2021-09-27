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
interface AssignedChatInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID             = 'entity_id';
    const AGENT_ID              = 'agent_id';
    const AGENT_UNIQUE_ID       = 'agent_unique_id';
    const CUSTOMER_ID           = 'customer_id';
    const UNIQUE_ID             = 'unique_id';
    const CHAT_STATUS           = 'chat_status';
    const IS_ADMIN_CHATTING     = 'is_admin_chatting';
    const ASSIGNED_AT     = 'assigned_at';
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
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get customer unique ID
     *
     * @return string|null
     */
    public function getUniqueId();

    /**
     * Get chat status
     *
     * @return int|null
     */
    public function getChatStatus();

    /**
     * Get chat status
     *
     * @return int|null
     */
    public function getIsAdminChatting();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setId($id);

    /**
     * Set agent ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setAgentId($agentId);

    /**
     * Set customer ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setAgentUniqueId($uniqueId);

     /**
      * Set customer ID
      *
      * @param int $id
      * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
      */
    public function setCustomerId($customerId);

    /**
     * Set customer ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setUniqueId($uniqueId);

    /**
     * Set chat status
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setChatStatus($status);

    /**
     * Get chat status
     *
     * @return int|null
     */
    public function setIsAdminChatting($chat);

    /**
     * Get assigned at
     *
     * @return string|null
     */
    public function getAssignedAt();

    /**
     * Get assigned at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Date
     *
     * @param string $date
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setAssignedAt($date);

    /**
     * Set Date
     *
     * @param string $date
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setCreatedAt($date);
}
