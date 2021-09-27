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
namespace Webkul\MagentoChatSystem\Api\Data;

interface AgentRatingInterface
{

    const RATING_COMMENT = 'rating_comment';
    const ENTITY_ID = 'entity_id';
    const RATING = 'rating';
    const AGENT_UNIQUE_ID = 'agent_unique_id';
    const AGENT_ID = 'agent_id';
    const CUSTOMER_ID = 'customer_id';
    const STATUS = 'status';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getId();

    /**
     * Set entity_id
     * @param string $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setId($id);

    /**
     * Get agent_id
     * @return string|null
     */
    public function getAgentId();

    /**
     * Set agent_id
     * @param string $agentId
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setAgentId($agentId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get agent_unique_id
     * @return string|null
     */
    public function getAgentUniqueId();

    /**
     * Set agent_unique_id
     * @param string $agentUniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setAgentUniqueId($agentUniqueId);

    /**
     * Get rating
     * @return string|null
     */
    public function getRating();

    /**
     * Set rating
     * @param string $rating
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setRating($rating);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setStatus($status);

    /**
     * Get rating_comment
     * @return string|null
     */
    public function getRatingComment();

    /**
     * Set rating_comment
     * @param string $ratingComment
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     */
    public function setRatingComment($ratingComment);
}
