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
namespace Webkul\DeliveryBoy\Api\Data;

interface OrderInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = "id";
    const OTP = "otp";
    const ORDER_ID = "order_id";
    const INCREMENT_ID = "increment_id";
    const ORDER_STATUS = "order_status";
    const ASSIGN_STATUS = "assign_status";
    const DELIVERYBOY_ID = "deliveryboy_id";

    /**
     * @return int|null
     */
    public function getId();
    
    /**
     * @param int $id
     * @return self
     */
    public function setId($id);
    
    /**
     * @return string|null
     */
    public function getOtp();
    
    /**
     * @param string $otp
     * @return self
     */
    public function setOtp($otp);
    
    /**
     * @return int
     */
    public function getOrderId();
    
    /**
     * @param int $orderId
     * @return self
     */
    public function setOrderId($orderId);
    
    /**
     * @return string|null
     */
    public function getIncrementId();
    
    /**
     * @param string $incrementId
     * @return self
     */
    public function setIncrementId($incrementId);
    
    /**
     * @return string|null
     */
    public function getOrderStatus();
    
    /**
     * @param string $orderStatus
     * @return self
     */
    public function setOrderStatus($orderStatus);
    
    /**
     * @return string
     */
    public function getAssignStatus();
    
    /**
     * @param string $assignStatus
     * @return self
     */
    public function setAssignStatus($assignStatus);
    
    /**
     * @return int
     */
    public function getDeliveryboyId();
    
    /**
     * @param int $deliveryboyId
     * @return self
     */
    public function setDeliveryboyId($deliveryboyId);
}
