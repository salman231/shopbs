<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Api\Data;

interface AllrmaInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RMA_ID                    = 'rma_id';
    const ORDER_ID                  = 'order_id';
    const GROUP                     = 'group';
    const INCREMENT_ID              = 'increment_id';
    const CUSTOMER_ID               = 'customer_id';
    const PACKAGE_CONDITION         = 'package_condition';
    const RESOLUTION_TYPE           = 'resolution_type';
    const ADDITIONAL_INFO           = 'additional_info';
    const CUSTOMER_DELIVERY_STATUS  = 'customer_delivery_status';
    const CUSTOMER_CONSIGNMENT_NO   = 'customer_consignment_no';
    const ADMIN_DELIVERY_STATUS     = 'admin_delivery_status';
    const ADMIN_CONSIGNMENT_NO      = 'admin_consignment_no';
    const IMAGES                    = 'images';
    const SHIPPING_LABEL            = 'shipping_label';
    const GUEST_EMAIL               = 'guest_email';
    const CREATED_AT                = 'created_at';
    const CUSTOMER_NAME             = 'name';
    const STATUS                    = 'status';
    const ADMIN_STATUS              = 'admin_status';
    const FINAL_STATUS              = 'final_status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Order ID Key
     *
     * @return string
     */
    public function getOrderId();

     /**
      * Get Group Name
      *
      * @return string
      */
    public function getGroup();

    /**
     * Get Increment ID
     *
     * @return string|null
     */
    public function getIncrementId();
    /**
     * Get CUSTOMER ID
     *
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Get Package Condition
     *
     * @return string|null
     */
    public function getPackageCondition();

    /**
     * Get Resolution Type
     *
     * @return string|null
     */
    public function getResolutionType();

     /**
      * Get Additional Info
      *
      * @return string|null
      */
    public function getAdditionalInfo();

    /**
     * Get Customer Delivery Status
     *
     * @return string|null
     */
    public function getCustomerDeliveryStatus();

     /**
      * Get Customer Consignment No
      *
      * @return string|null
      */
    public function getCustomerConsignmentNo();

    /**
     * Get Admin Delivery Status
     *
     * @return string|null
     */
    public function getAdminDeliveryStatus();
    
     /**
      * Get Admin Consignment No
      *
      * @return string|null
      */
    public function getAdminConsignmentNo();

    /**
     * Get Images
     *
     * @return string|null
     */
    public function getImages();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get Shipping Label
     *
     * @return string|null
     */
    public function getShippingLabel();

     /**
      * Get Guest Email
      *
      * @return string|null
      */
    public function getGuestEmail();

    /**
     * Get customer name
     *
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Get admin status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get admin status
     *
     * @return int|null
     */
    public function getAdminStatus();

    /**
     * Get final rma status
     *
     * @return int|null
     */
    public function getFinalStatus();

    /**
     * Get ID
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setId($id);

    /**
     * Set Order ID
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setOrderId($orderId);

    /**
     * Set Group Name
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setGroup($group);

    /**
     * Set Increment ID
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setIncrementId($incrementId);

    /**
     * Set CUSTOMER ID
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setCustomerId($customerId);

    /**
     * Set Package Condition
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setPackageCondition($condition);

    /**
     * Set Resolution Type
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setResolutionType($resolutionType);

     /**
      * Set Additional Info
      *
      * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
      */
    public function setAdditionalInfo($additionalInfo);

    /**
     * Set Customer Delivery Status
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setCustomerDeliveryStatus($customerDeliveryStatus);
     /**
      * Set Customer Consignment No
      *
      * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
      */
    public function setCustomerConsignmentNo($customerConsignmentNo);
    /**
     * Set Admin Delivery Status
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setAdminDeliveryStatus($adminDeliveryStatus);
     /**
      * Set Admin Consignment No
      *
      * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
      */
    public function setAdminConsignmentNo($adminConsignmentNo);
    /**
     * Set Images
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setImages($images);

    /**
     * Set creation time
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Shipping Label
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setShippingLabel($shippingLabel);
     /**
      * Set Guest Email
      *
      * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
      */
    public function setGuestEmail($guestEmail);

    /**
     * Set customer name
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setCustomerName($name);

    /**
     * Set rma status
     *
     * @return int|null
     */
    public function setStatus($status);

    /**
     * Set admin status
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setAdminStatus($status);

    /**
     * Set final rma status
     *
     * @return  \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setFinalStatus($status);
}
