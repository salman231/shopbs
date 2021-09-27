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

use Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface;

class AgentRating extends \Magento\Framework\Model\AbstractModel implements AgentRatingInterface
{

    protected $_eventPrefix = 'webkul_magentochatsystem_agentrating';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MagentoChatSystem\Model\ResourceModel\AgentRating::class);
    }

    /**
     * Get agentrating_id
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set agentrating_id
     * @param string $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get agent_id
     * @return string
     */
    public function getAgentId()
    {
        return $this->getData(self::AGENT_ID);
    }

    /**
     * Set agent_id
     * @param string $agentId
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setAgentId($agentId)
    {
        return $this->setData(self::AGENT_ID, $agentId);
    }

    /**
     * Get customer_id
     * @return string
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }
    
    /**
     * Get agent_unique_id
     * @return string
     */
    public function getAgentUniqueId()
    {
        return $this->getData(self::AGENT_UNIQUE_ID);
    }

    /**
     * Set agent_unique_id
     * @param string $agentUniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setAgentUniqueId($agentUniqueId)
    {
        return $this->setData(self::AGENT_UNIQUE_ID, $agentUniqueId);
    }

    /**
     * Get rating
     * @return string
     */
    public function getRating()
    {
        return $this->getData(self::RATING);
    }

    /**
     * Set rating
     * @param string $rating
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setRating($rating)
    {
        return $this->setData(self::RATING, $rating);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get rating_comment
     * @return string
     */
    public function getRatingComment()
    {
        return $this->getData(self::RATING_COMMENT);
    }

    /**
     * Set rating_comment
     * @param string $ratingComment
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setRatingComment($ratingComment)
    {
        return $this->setData(self::RATING_COMMENT, $ratingComment);
    }
}
