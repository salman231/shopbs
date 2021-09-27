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

use Webkul\MagentoChatSystem\Api\Data\AgentDataInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\MagentoChatSystem\Model\ResourceModel\AgentData as AgentDataResourceModel;

/**
 * Customer data model
 *
 */
class AgentData extends AbstractModel implements AgentDataInterface, IdentityInterface
{
    /**
     * Customer data cache tag
     */
    const CACHE_TAG = 'chat_agent_data';

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
    protected $_cacheTag = 'chat_agent_data';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'chat_agent_data';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AgentDataResourceModel::class);
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
     * Get chat status
     *
     * @return int|null
     */
    public function getChatStatus()
    {
        return $this->getData(self::CHAT_STATUS);
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
     * Set chat status
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     */
    public function setChatStatus($status)
    {
        return $this->setData(self::CHAT_STATUS, $status);
    }
}
