<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\DeliveryBoy\Api\Data\OrderInterface;

class Order extends AbstractModel implements OrderInterface, IdentityInterface
{
     /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    const CACHE_TAG = "expressdelivery_order";
    
    /**
     * Default Id for when id field value is null
     */
    const NOROUTE_ID = "no-route";

    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_order";
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_order";

    /**
     * Initialize Model object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\DeliveryBoy\Model\ResourceModel\Order::class);
    }

    /**
     * @param int|null $id
     * @param int|null $field
     * @return self
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteOrder();
        }
        return parent::load($id, $field);
    }

    /**
     * Load object with noroute id data
     *
     * @return self
     */
    public function noRouteOrder()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * Return array of name of object in cache
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . "_" . $this->getId()];
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return parent::getData(self::ID);
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return string|null
     */
    public function getOtp()
    {
        return parent::getData(self::OTP);
    }

    /**
     * @param string $otp
     * @return self
     */
    public function setOtp($otp)
    {
        return $this->setData(self::OTP, $otp);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     * @return self
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return string|null
     */
    public function getIncrementId()
    {
        return parent::getData(self::INCREMENT_ID);
    }

    /**
     * @param string $incrementId
     * @return self
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * @return string|null
     */
    public function getOrderStatus()
    {
        return parent::getData(self::ORDER_STATUS);
    }

    /**
     * @param string $orderStatus
     * @return self
     */
    public function setOrderStatus($orderStatus)
    {
        return $this->setData(self::ORDER_STATUS, $orderStatus);
    }

    /**
     * @return string
     */
    public function getAssignStatus()
    {
        return parent::getData(self::ASSIGN_STATUS);
    }

    /**
     * @param string $assignStatus
     * @return self
     */
    public function setAssignStatus($assignStatus)
    {
        return $this->setData(self::ASSIGN_STATUS, $assignStatus);
    }

    /**
     * @return int
     */
    public function getDeliveryboyId()
    {
        return parent::getData(self::DELIVERYBOY_ID);
    }

    /**
     * @param int $deliveryboyId
     * @return self
     */
    public function setDeliveryboyId($deliveryboyId)
    {
        return $this->setData(self::DELIVERYBOY_ID, $deliveryboyId);
    }
}
