<?php
namespace Webkul\Rmasystem\Model;

use Webkul\Rmasystem\Api\Data\AllrmaInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Rmasystem Allrma Model
 *
 */
class Allrma extends \Magento\Framework\Model\AbstractModel implements AllrmaInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Product's Statuses
     */
    const STATUS_PENDING = 0;
    const STATUS_CANCEL = 4;
    const STATUS_DECLINE = 3;
    const STATUS_SOLVE = 2;
    const STATUS_PROCESSING = 1;
    /**#@-*/
    /**
     * Customer entity
     *
     * @var Customer
     */
    protected $_customer;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Marketplace Product cache tag
     */
    const CACHE_TAG = 'rmasystem_allrma';

    /**
     * @var string
     */
    protected $_cacheTag = 'rmasystem_allrma';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rmasystem_allrma';

    /**
     * Initialize resource model
     *
     * @return void
     */
    /*public function __construct(\Magento\Customer\Model\CustomerFactory $customerFactory){
        $this->_customerFactory = $customerFactory;
    }*/
    protected function _construct()
    {
        $this->_init(\Webkul\Rmasystem\Model\ResourceModel\Allrma::class);
    }

    /**
     * Prepare product's statuses.
     * Available event marketplace_product_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_PROCESSING => __('Processing'),
            self::STATUS_DECLINE => __('Decline'),
            self::STATUS_SOLVE => __('Solved'),
            self::STATUS_CANCEL => __('Cancelled')
        ];
    }

    /**
     * Get identities
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
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::RMA_ID);
    }
    /**
     * Get ID
     *
     * @return int
     */
    public function getOrderId()
    {
        return parent::getData(self::ORDER_ID);
    }

    /**
     * Get Increment ID
     *
     * @return string|null
     */
    public function getIncrementId()
    {
        return parent::getData(self::INCREMENT_ID);
    }
    /**
     * Get group name
     *
     * @return int
     */
    public function getGroup()
    {
        return parent::getData(self::GROUP);
    }
    /**
     * Get CUSTOMER ID
     *
     * @return int
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }
    /**
     * Set group name
     *
     * @return int
     */
    public function setGroup($group)
    {
        return parent::setData(self::GROUP, $group);
    }

     /**
      * @return string
      */
    public function getCustomer()
    {
        if ($this->getCustomerId()) {
            $customerName = $this->_objectManager->create(\Magento\Customer\Model\Customer::class)
                        ->load($this->getCustomerId)->getName();
        } else {
            $customerName = (string)__('Guest');
        }
        return $customerName;
    }

    /**
     * Get Package Condition
     *
     * @return string|null
     */
    public function getPackageCondition()
    {
        return parent::getData(self::PACKAGE_CONDITION);
    }

    /**
     * Get Resolution Type
     *
     * @return string|null
     */
    public function getResolutionType()
    {
        return parent::getData(self::RESOLUTION_TYPE);
    }

     /**
      * Get Additional Info
      *
      * @return string|null
      */
    public function getAdditionalInfo()
    {
        return parent::getData(self::ADDITIONAL_INFO);
    }

    /**
     * Get Customer Delivery Status
     *
     * @return string|null
     */
    public function getCustomerDeliveryStatus()
    {
        return parent::getData(self::CUSTOMER_DELIVERY_STATUS);
    }
     /**
      * Get Customer Consignment No
      *
      * @return string|null
      */
    public function getCustomerConsignmentNo()
    {
        return parent::getData(self::CUSTOMER_CONSIGNMENT_NO);
    }
    /**
     * Get Admin Delivery Status
     *
     * @return string|null
     */
    public function getAdminDeliveryStatus()
    {
        return parent::getData(self::ADMIN_DELIVERY_STATUS);
    }
     /**
      * Get Admin Consignment No
      *
      * @return string|null
      */
    public function getAdminConsignmentNo()
    {
        return parent::getData(self::ADMIN_CONSIGNMENT_NO);
    }
    /**
     * Get Images
     *
     * @return string|null
     */
    public function getImages()
    {
        return parent::getData(self::IMAGES);
    }

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Get Shipping Label
     *
     * @return string|null
     */
    public function getShippingLabel()
    {
        return parent::getData(self::SHIPPING_LABEL);
    }
     /**
      * Get Guest Email
      *
      * @return string|null
      */
    public function getGuestEmail()
    {
        return parent::getData(self::GUEST_EMAIL);
    }

    /**
     * Get customer name
     *
     * @return string|null
     */
    public function getCustomerName()
    {
        return parent::getData(self::CUSTOMER_NAME);
    }

    /**
     * get rma status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Get admin status
     *
     * @return int|null
     */
    public function getAdminStatus()
    {
        return parent::getData(self::ADMIN_STATUS);
    }

    /**
     * Get final rma status
     *
     * @return int|null
     */
    public function getFinalStatus()
    {
        return parent::getData(self::FINAL_STATUS);
    }

    /**
     * Get ID
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setId($id)
    {
        return $this->setData(self::RMA_ID, $id);
    }

    /**
     * Set Order ID Key
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set Increment ID
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }
    /**
     * Set CUSTOMER ID
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Set Package Condition
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setPackageCondition($condition)
    {
        return $this->setData(self::PACKAGE_CONDITION, $condition);
    }

    /**
     * Set Resolution Type
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setResolutionType($resolutionType)
    {
        return $this->setData(self::RESOLUTION_TYPE, $resolutionType);
    }

     /**
      * Set Additional Info
      *
      * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
      */
    public function setAdditionalInfo($additionalInfo)
    {
        return $this->setData(self::ADDITIONAL_INFO, $additionalInfo);
    }

    /**
     * Set Customer Delivery Status
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setCustomerDeliveryStatus($customerDeliveryStatus)
    {
        return $this->setData(self::CUSTOMER_DELIVERY_STATUS, $customerDeliveryStatus);
    }
     /**
      * Set Customer Consignment No
      *
      * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
      */
    public function setCustomerConsignmentNo($customerConsignmentNo)
    {
        return $this->setData(self::CUSTOMER_CONSIGNMENT_NO, $customerConsignmentNo);
    }
    /**
     * Set Admin Delivery Status
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setAdminDeliveryStatus($adminDeliveryStatus)
    {
        return $this->setData(self::ADMIN_DELIVERY_STATUS, $adminDeliveryStatus);
    }
     /**
      * Set Admin Consignment No
      *
      * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
      */
    public function setAdminConsignmentNo($adminConsignmentNo)
    {
        return $this->setData(self::ADMIN_CONSIGNMENT_NO, $adminConsignmentNo);
    }
    /**
     * Set Images
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setImages($images)
    {
        return $this->setData(self::IMAGES, $images);
    }

    /**
     * Set creation time
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set Shipping Label
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setShippingLabel($shippingLabel)
    {
        return $this->setData(self::SHIPPING_LABEL, $shippingLabel);
    }
     /**
      * Set Guest Email
      *
      * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
      */
    public function setGuestEmail($guestEmail)
    {
        return $this->setData(self::GUEST_EMAIL, $guestEmail);
    }

    /**
     * Set customer name
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setCustomerName($name)
    {
        return $this->setData(self::CUSTOMER_NAME, $name);
    }

    /**
     * Set rma status
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set admin status
     *
     * @return \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setAdminStatus($status)
    {
        return $this->setData(self::ADMIN_STATUS, $status);
    }

    /**
     * Set final rma status
     *
     * @return  \Webkul\Rmasystem\Api\Data\AllrmaInterface
     */
    public function setFinalStatus($status)
    {
        return $this->setData(self::FINAL_STATUS, $status);
    }
}
