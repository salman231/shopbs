<?php
namespace Webkul\Rmasystem\Model;

use Webkul\Rmasystem\Api\Data\RmaitemInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Rmaitem extends \Magento\Framework\Model\AbstractModel implements RmaitemInterface, IdentityInterface
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
    const CACHE_TAG = 'webkul_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'webkul_item';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webkul_item';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\ResourceModel\Rmaitem::class);
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
     * Get RMA ID
     *
     * @return int
     */
    public function getRmaId()
    {
        return $this->getData(self::RMA_ID);
    }
    /**
     * Get Item ID
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }
    /**
     * Get Reason ID
     *
     * @return int
     */
    public function getReasonId()
    {
        return $this->getData(self::REASON_ID);
    }

    /**
     * Get Status
     *
     * @return decimal
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
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
     * Set Item ID
     *
     * @return int
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
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
     * Set Reason ID
     *
     * @return string
     */
    public function setReasonId($reasonId)
    {
        return $this->setData(self::REASON_ID, $reasonId);
    }
    /**
     * set return qty
     * @param price
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }
}
