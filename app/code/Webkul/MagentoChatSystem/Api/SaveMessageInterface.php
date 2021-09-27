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
 
interface SaveMessageInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param int $senderId
     * @param int $receiverId
     * @param string $receiverUniqueId
     * @param string $message
     * @param string $dateTime
     * @param string $msgType
     * @return string Greeting message with users name.
     */
    public function saveMeassage($senderId, $receiverId, $receiverUniqueId, $message, $dateTime, $msgType = '');
}
