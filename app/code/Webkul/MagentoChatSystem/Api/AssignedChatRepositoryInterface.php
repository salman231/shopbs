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
 * assigned chat block CRUD interface.
 * @api
 */
interface AssignedChatRepositoryInterface
{
    /**
     * Save message history.
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface $items
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\AssignedChatInterface $items);

    /**
     * Retrieve message by id.
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Delete message.
     *
     * @param \Webkul\MagentoChatSystem\Api\Data\AssignedChatInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\AssignedChatInterface $message);

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
