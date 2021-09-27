<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Model;

use Webkul\MagentoChatSystem\Api\Data\TotalAssignedChatInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\MagentoChatSystem\Model\ResourceModel\TotalAssignedChat as TotalAssignedChatResourceModel;

/**
 * Assigned chat data model
 *
 */
class TotalAssignedChat extends AbstractModel implements TotalAssignedChatInterface, IdentityInterface
{
    /**
     * assigned chat data cache tag
     */
    const CACHE_TAG = 'total_assigned_chat';

    /**#@+
     * customer chat statuses
     */
    const STATUS_BUSY = 2;
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'total_assigned_chat';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'total_assigned_chat';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(TotalAssignedChatResourceModel::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get admin ID
     *
     * @return int|null
     */
    public function getAgentId()
    {
        return $this->getData(self::AGENT_ID);
    }

    /**
     * Get admin unique ID
     *
     * @return string|null
     */
    public function getAgentUniqueId()
    {
        return $this->getData(self::AGENT_UNIQUE_ID);
    }

    /**
     * Get total active chat
     *
     * @return int|null
     */
    public function getTotalActiveChat()
    {
        return $this->getData(self::TOTAL_ACTIVE_CHAT);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

     /**
      * Set customer ID
      *
      * @param int $id
      * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
      */
    public function setAgentId($agentId)
    {
        return $this->setData(self::AGENT_ID, $agentId);
    }

    /**
     * Set customer ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     */
    public function setAgentUniqueId($uniqueId)
    {
        return $this->setData(self::AGENT_UNIQUE_ID, $uniqueId);
    }

    /**
     * Set total active chat ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\TotalAssignedChatInterface
     */
    public function setTotalActiveChat($activeChat)
    {
        return $this->setData(self::TOTAL_ACTIVE_CHAT, $activeChat);
    }
}
