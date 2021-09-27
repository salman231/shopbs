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
use Webkul\DeliveryBoy\Api\Data\DeliveryboyInterface;

class Deliveryboy extends AbstractModel implements DeliveryboyInterface, IdentityInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const TYPE_BYKE = "bike";
    const TYPE_CYCLE = "cycle";
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    /**
     * Default Id for when id field value is null
     */
    const NOROUTE_ID = "no-route";
    
    /**
     * Tag to associate cache entries with
     */
    const CACHE_TAG = "expressdelivery_deliveryboy";
    
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_deliveryboy";

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_deliveryboy";

    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy\Validator\CompositeValidator
     */
    private $validator;

    /**
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy\Validator\CompositeValidator $validator
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy $resource
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Webkul\DeliveryBoy\Model\Deliveryboy\Validator\CompositeValidator $validator,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy $resource = null,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\Collection $resourceCollection = null,
        array $data = []
    ) {
        $this->validator = $validator;
        
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize model object
     */
    protected function _construct()
    {
        $this->_init(\Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy::class);
    }

    /**
     * Load object data
     *
     * @param int $id
     * @param null|string $field
     * @return self
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteDeliveryboy();
        }
        return parent::load($id, $field);
    }

    /**
     * @return self
     */
    public function noRouteDeliveryboy()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED  => __("Enabled"),
            self::STATUS_DISABLED => __("Disabled")
        ];
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return [
            self::TYPE_BYKE  => __("Bike"),
            self::TYPE_CYCLE => __("Cycle")
        ];
    }

    /**
     * Return array of name of object in cache
     *
     * @return string[]
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
     * @param  int $id
     * @return self
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return parent::getData(self::NAME);
    }

    /**
     * @param  string $name
     * @return self
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return parent::getData(self::EMAIL);
    }

    /**
     * @param  string $email
     * @return self
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @return string|null
     */
    public function getImage()
    {
        return parent::getData(self::IMAGE);
    }

    /**
     * @param  string $image
     * @return self
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * @return int|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * @param  int $status
     * @return self
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return parent::getData(self::ADDRESS);
    }

    /**
     * @param  string $address
     * @return self
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * @return string|null
     */
    public function getLatitude()
    {
        return parent::getData(self::LATITUDE);
    }

    /**
     * @param  string $latitude
     * @return self
     */
    public function setLatitude($latitude)
    {
        return $this->setData(self::LATITUDE, $latitude);
    }

    /**
     * @return string|null
     */
    public function getLongitude()
    {
        return parent::getData(self::LONGITUDE);
    }
    /**
     * @param  string $longitude
     * @return self
     */
    public function setLongitude($longitude)
    {
        return $this->setData(self::LONGITUDE, $longitude);
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return parent::getData(self::PASSWORD);
    }

    /**
     * @param  string $password
     * @return self
     */
    public function setPassword($password)
    {
        return $this->setData(self::PASSWORD, $password);
    }

    /**
     * @return string|null
     */
    public function getRpToken()
    {
        return parent::getData(self::RP_TOKEN);
    }

    /**
     * @param  string $rpToken
     * @return self
     */
    public function setRpToken($rpToken)
    {
        return $this->setData(self::RP_TOKEN, $rpToken);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * @param  string $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    /**
     * @param  string $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @return string
     */
    public function getVehicleType()
    {
        return parent::getData(self::VEHICLE_TYPE);
    }

    /**
     * @param  string $vehicleType
     * @return self
     */
    public function setVehicleType($vehicleType)
    {
        return $this->setData(self::VEHICLE_TYPE, $vehicleType);
    }

    /**
     * @return string|null
     */
    public function getMobileNumber()
    {
        return parent::getData(self::MOBILE_NUMBER);
    }

    /**
     * @param  string $mobileNumber
     * @return self
     */
    public function setMobileNumber($mobileNumber)
    {
        return $this->setData(self::MOBILE_NUMBER, $mobileNumber);
    }

    /**
     * @return string|null
     */
    public function getVehicleNumber()
    {
        return parent::getData(self::VEHICLE_NUMBER);
    }

    /**
     * @param  string $vehicleNumber
     * @return self
     */
    public function setVehicleNumber($vehicleNumber)
    {
        return $this->setData(self::VEHICLE_NUMBER, $vehicleNumber);
    }

    /**
     * @return string
     */
    public function getRpTokenCreatedAt()
    {
        return parent::getData(self::RP_TOKEN_CREATED_AT);
    }

    /**
     * @param  string $rpTokenCreatedAt
     * @return self
     */
    public function setRpTokenCreatedAt($rpTokenCreatedAt)
    {
        return $this->setData(self::RP_TOKEN_CREATED_AT, $rpTokenCreatedAt);
    }

    /**
     * @return int|null
     */
    public function getAvailabilityStatus()
    {
        return parent::getData(self::AVAILABILITY_STATUS);
    }

    /**
     * @param int $availabilityStatus
     * @return self
     */
    public function setAvailabilityStatus($availabilityStatus)
    {
        return $this->setData(self::AVAILABILITY_STATUS, $availabilityStatus);
    }

    /**
     * @return \Zend_Validate_Interface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
