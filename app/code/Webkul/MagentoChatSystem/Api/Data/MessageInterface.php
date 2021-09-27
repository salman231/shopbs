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
namespace Webkul\MagentoChatSystem\Api\Data;

/**
 * MagentoChatSystem customer interface.
 * @api
 */
interface MessageInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID     = 'entity_id';
    const SENDER_ID     = 'sender_id';
    const RECEIVER_ID   = 'receiver_id';
    const SENDER     = 'sender_name';
    const RECEIVER   = 'receiver_name';
    const MESSAGE       = 'message';
    const DATE          = 'date';
    const SENDER_UNIQUE_ID = 'sender_unique_id';
    const RECEIVER_UNIQUE_ID = 'receiver_unique_id';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get sender ID
     *
     * @return int|null
     */
    public function getSenderId();

    /**
     * Get sender ID
     *
     * @return string|null
     */
    public function getSenderUniqueId();

     /**
      * Get receiver ID
      *
      * @return int|null
      */
    public function getReceiverId();

    /**
     * Get receiver ID
     *
     * @return string|null
     */
    public function getReceiverUniqueId();

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getMessage();

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getDate();

    /**
     * Get Sender Name
     *
     * @return string|null
     */
    public function getSender();

    /**
     * Get Receiver Name
     *
     * @return string|null
     */
    public function getReceiver();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setId($id);

    /**
     * Set sender id
     *
     * @param string $sender
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setSender($sender);

    /**
     * Set sender id
     *
     * @param string $receiver
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setReceiver($receiver);

    /**
     * Set sender id
     *
     * @param int $senderId
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setSenderId($senderId);

     /**
      * Set sender id
      *
      * @param string $senderId
      * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
      */
    public function setSenderUniqueId($senderUniqueId);

    /**
     * Set receiver id
     *
     * @param int $receiverId
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setReceiverId($receiverId);

    /**
     * Set receiver unique id
     *
     * @param string $senderUniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setReceiverUniqueId($receiverUniqueId);

    /**
     * Set receiver id
     *
     * @param string $message
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setMessage($message);

    /**
     * Set date
     *
     * @param string $date
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setDate($date);
}
