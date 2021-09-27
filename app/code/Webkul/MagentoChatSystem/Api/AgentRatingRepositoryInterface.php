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
namespace Webkul\MagentoChatSystem\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface AgentRatingRepositoryInterface
{

    /**
     * Save AgentRating
     * @param \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface $agentRating
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface $agentRating
    );

    /**
     * Retrieve AgentRating
     * @param string $agentratingId
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($agentratingId);

    /**
     * Retrieve AgentRating matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentRatingSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete AgentRating
     * @param \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface $agentRating
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Webkul\MagentoChatSystem\Api\Data\AgentRatingInterface $agentRating
    );

    /**
     * Delete AgentRating by ID
     * @param string $agentratingId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($agentratingId);
}
