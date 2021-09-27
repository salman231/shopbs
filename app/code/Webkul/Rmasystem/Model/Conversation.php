<?php
namespace Webkul\Rmasystem\Model;

use Webkul\Rmasystem\Api\Data\ConversationInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Conversation extends \Magento\Framework\Model\AbstractModel implements ConversationInterface, IdentityInterface
{

    /**#@+
     * Post's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'webkul_conversation_collection';

    /**
     * @var string
     */
    protected $_cacheTag = 'webkul_conversation_collection';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_conversation_collection';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\ResourceModel\Conversation::class);
    }

    /**
     * Prepare post's statuses.
     * Available event to customize statuses.
     *
     * @return array
     */
    /*public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }*/
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }
    /**
     * Get RMA ID
     *
     * @return int
     */
    public function getRmaId()
    {
        return $this->getData(self::RMA_ID);
    }
    /**
     * Get Messaage
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }
    /**
     * Get Creating time
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get Sender
     *
     * @return string
     */
    public function getSender()
    {
        return $this->getData(self::SENDER);
    }
    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Rmasystem\Api\Data\RmaitemInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
    /**
     * Set RMA ID
     *
     * @return string
     */
    public function setRmaId($rmaId)
    {
        return $this->setData(self::RMA_ID, $rmaId);
    }
    /**
     * Set Item ID
     *
     * @return int
     */
    public function setMessage($messaage)
    {
        return $this->setData(self::MESSAGE, $messaage);
    }
    /**
     * Set Creating time
     *
     * @return string
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
    /**
     * set return qty
     * @param price
     */
    public function setSender($sender)
    {
        return $this->setData(self::SENDER, $sender);
    }
}
