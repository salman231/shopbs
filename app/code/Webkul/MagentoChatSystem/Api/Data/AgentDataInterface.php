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
 * MagentoChatSystem customer interface.
 * @api
 */
interface AgentDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID             = 'entity_id';
    const AGENT_ID              = 'agent_id';
    const AGENT_UNIQUE_ID       = 'agent_unique_id';
    const CHAT_STATUS           = 'chat_status';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get admin ID
     *
     * @return int|null
     */
    public function getAgentId();

    /**
     * Get admin unique ID
     *
     * @return string|null
     */
    public function getAgentUniqueId();

    /**
     * Get chat status
     *
     * @return int|null
     */
    public function getChatStatus();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     */
    public function setId($id);

     /**
      * Set customer ID
      *
      * @param int $id
      * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
      */
    public function setAgentId($agentId);

    /**
     * Set customer ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     */
    public function setAgentUniqueId($uniqueId);

    /**
     * Set chat status
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     */
    public function setChatStatus($status);
}
