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

use Webkul\MagentoChatSystem\Api\Data\MessageInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\MagentoChatSystem\Model\ResourceModel\Message as MessageResourceModel;

/**
 * Message history model
 *
 */
class Message extends AbstractModel implements MessageInterface, IdentityInterface
{
    /**
     * Message history cache tag
     */
    const CACHE_TAG = 'chat_message_history';

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
    protected $_cacheTag = 'chat_message_history';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'chat_message_history';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(MessageResourceModel::class);
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
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get sender ID
     *
     * @return int|null
     */
    public function getSenderId()
    {
        return $this->getData(self::SENDER_ID);
    }

    /**
     * Get sender ID
     *
     * @return string|null
     */
    public function getSenderUniqueId()
    {
        return $this->getData(self::SENDER_UNIQUE_ID);
    }

     /**
      * Get receiver ID
      *
      * @return int|null
      */
    public function getReceiverId()
    {
        return $this->getData(self::RECEIVER_ID);
    }

    /**
     * Get receiver ID
     *
     * @return string|null
     */
    public function getReceiverUniqueId()
    {
        return $this->getData(self::RECEIVER_UNIQUE_ID);
    }

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * Get Sender Name
     *
     * @return string|null
     */
    public function getSender()
    {
        return $this->getData(self::SENDER);
    }

    /**
     * Get Receiver Name
     *
     * @return string|null
     */
    public function getReceiver()
    {
        return $this->getData(self::RECEIVER);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set sender id
     *
     * @param string $sender
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setSender($sender)
    {
        return $this->setData(self::SENDER, $sender);
    }

    /**
     * Set sender id
     *
     * @param string $receiver
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setReceiver($receiver)
    {
        return $this->setData(self::RECEIVER, $receiver);
    }

    /**
     * Set sender id
     *
     * @param int $senderId
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setSenderId($senderId)
    {
        return $this->setData(self::SENDER_ID, $senderId);
    }

    /**
     * Set sender unique id
     *
     * @param string $senderUniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setSenderUniqueId($senderUniqueId)
    {
        return $this->setData(self::SENDER_UNIQUE_ID, $senderUniqueId);
    }

    /**
     * Set receiver id
     *
     * @param int $receiverId
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setReceiverId($receiverId)
    {
        return $this->setData(self::RECEIVER_ID, $receiverId);
    }

    /**
     * Set receiver unique id
     *
     * @param string $senderUniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setReceiverUniqueId($receiverUniqueId)
    {
        return $this->setData(self::RECEIVER_UNIQUE_ID, $receiverUniqueId);
    }

    /**
     * Set receiver id
     *
     * @param string $message
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Set date
     *
     * @param string $date
     * @return \Webkul\MagentoChatSystem\Api\Data\MessageInterface
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }
}
