<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Api;

/**
 * Rma Conversation CRUD interface
 * @api
 */
interface ConversationRepositoryInterface
{
    /**
     * Save Conversation.
     *
     * @param Webkul\Rmasystem\Api\Data\ConversationInterface $conversation
     * @return Webkul\Rmasystem\Api\Data\ConversationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\Rmasystem\Api\Data\ConversationInterface $conversation);

    /**
     * Get Conversation by ID.
     *
     * @param int $id
     * @return Webkul\Rmasystem\Api\Data\ConversationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $rmaId is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve conversation list.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Webkul\Rmasystem\Api\Data\ConversationSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete conversation.
     *
     * @param Webkul\Rmasystem\Api\Data\ConversationInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\StateException If rma cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\Rmasystem\Api\Data\ConversationInterface $group);

    /**
     * Delete conversation by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException If rma cannot be deleted
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
