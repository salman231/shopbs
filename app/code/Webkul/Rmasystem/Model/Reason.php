<?php
namespace Webkul\Rmasystem\Model;

use Webkul\Rmasystem\Api\Data\ReasonInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Reason extends \Magento\Framework\Model\AbstractModel implements ReasonInterface, IdentityInterface
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
    const CACHE_TAG = 'webkul_reason';

    /**
     * @var string
     */
    protected $_cacheTag = 'webkul_reason';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_reason';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\ResourceModel\Reason::class);
    }

    /**
     * Prepare post's statuses.
     * Available event to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
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
     * Get Reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->getData(self::REASON);
    }
     /**
      * Get Status
      *
      * @return boolen
      */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    /**
     * SET ID
     *
     * @return int|null
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
    /**
     * Get Reason
     *
     * @return string
     */
    public function setReason($reason)
    {
        return $this->setData(self::REASON, $reason);
    }
     /**
      * Get Status
      *
      * @return boolen
      */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
