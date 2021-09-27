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

use Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\MagentoChatSystem\Model\ResourceModel\CustomerData as CustomerDataResourceModel;

/**
 * Customer data model
 *
 */
class CustomerData extends AbstractModel implements CustomerDataInterface, IdentityInterface
{
    /**
     * Customer data cache tag
     */
    const CACHE_TAG = 'chat_customer_data';

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
    protected $_cacheTag = 'chat_customer_data';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'chat_customer_data';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CustomerDataResourceModel::class);
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
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Get customer unique ID
     *
     * @return string|null
     */
    public function getUniqueId()
    {
        return $this->getData(self::UNIQUE_ID);
    }

    /**
     * Get chat status
     *
     * @return int|null
     */
    public function getChatStatus()
    {
        return $this->getData(self::CHAT_STATUS);
    }

    /**
     * Get user image
     *
     * @return int|null
     */
    public function getImage()
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

     /**
      * Set customer ID
      *
      * @param int $id
      * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
      */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set customer ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setUniqueId($uniqueId)
    {
        return $this->setData(self::UNIQUE_ID, $uniqueId);
    }

    /**
     * Set chat status
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setChatStatus($status)
    {
        return $this->setData(self::CHAT_STATUS, $status);
    }
    /**
     * Set user image
     *
     * @param string $image
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }
}
