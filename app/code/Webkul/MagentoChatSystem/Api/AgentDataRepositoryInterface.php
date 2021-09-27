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
namespace Webkul\MagentoChatSystem\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * time slots block CRUD interface.
 * @api
 */
interface AgentDataRepositoryInterface
{
    /**
     * Save message history.
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface $items
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\AgentDataInterface $items);

    /**
     * Retrieve message by id.
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve message matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete message.
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\AgentDataInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\AgentDataInterface $message);

    /**
     * Delete message.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
