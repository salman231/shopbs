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

use Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\MagentoChatSystem\Model\ResourceModel\AssignedChat as AssignedChatResourceModel;

/**
 * Assigned chat data model
 *
 */
class AssignedChat extends AbstractModel implements AssignedChatInterface, IdentityInterface
{
    /**
     * assigned chat data cache tag
     */
    const CACHE_TAG = 'assigned_chat_data';

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
    protected $_cacheTag = 'assigned_chat_data';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'assigned_chat_data';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AssignedChatResourceModel::class);
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

    /**
     * Prepare post's statuses.
     * Available event to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ACTIVE => __('Online'), self::STATUS_DISABLED => __('Offline')];
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
      * Get customer ID
      *
      * @return int|null
      */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Get customer unique ID
     *
     * @return string|null
     */
    public function getUniqueId()
    {
        return $this->getData(self::UNIQUE_ID);
    }

    /**
     * Get chat status
     *
     * @return int|null
     */
    public function getChatStatus()
    {
        return $this->getData(self::CHAT_STATUS);
    }

    /**
     * Get chat status
     *
     * @return int|null
     */
    public function getIsAdminChatting()
    {
        return $this->getData(self::IS_ADMIN_CHATTING);
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
     * Set customer ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set customer ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setUniqueId($uniqueId)
    {
        return $this->setData(self::UNIQUE_ID, $uniqueId);
    }

    /**
     * Set chat status
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setChatStatus($status)
    {
        return $this->setData(self::CHAT_STATUS, $status);
    }

    /**
     * set chat status
     *
     * @return int|null
     */
    public function setIsAdminChatting($chat)
    {
        return $this->setData(self::IS_ADMIN_CHATTING, $chat);
    }

     /**
      * Get assigned at
      *
      * @return string|null
      */
    public function getAssignedAt()
    {
        return $this->getData(self::ASSIGNED_AT);
    }

    /**
     * Get assigned at
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::ASSIGNED_AT);
    }

    /**
     * Set Date
     *
     * @param string $date
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setAssignedAt($date)
    {
        return $this->setData(self::ASSIGNED_AT, $date);
    }

    /**
     * Set Date
     *
     * @param string $date
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     */
    public function setCreatedAt($date)
    {
        return $this->setData(self::ASSIGNED_AT, $date);
    }
}
