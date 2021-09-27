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

interface DeliveryboyInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = "id";
    const NAME = "name";
    const EMAIL = "email";
    const IMAGE = "image";
    const STATUS = "status";
    const ADDRESS = "address";
    const LATITUDE = "latitude";
    const PASSWORD = "password";
    const RP_TOKEN = "rp_token";
    const LONGITUDE = "longitude";
    const CREATED_AT = "created_at";
    const UPDATED_AT = "updated_at";
    const VEHICLE_TYPE = "vehicle_type";
    const MOBILE_NUMBER = "mobile_number";
    const VEHICLE_NUMBER = "vehicle_number";
    const RP_TOKEN_CREATED_AT = "rp_token_created_at";
    const AVAILABILITY_STATUS = "availability_status";

    /**
     * @return int|null
     */
    public function getId();
    
    /**
     * @param  int $id
     * @return self
     */
    public function setId($id);
    
    /**
     * @return string|null
     */
    public function getName();
    
    /**
     * @param  string $name
     * @return self
     */
    public function setName($name);
    
    /**
     * @return string|null
     */
    public function getEmail();
    
    /**
     * @param  string $email
     * @return self
     */
    public function setEmail($email);
    
    /**
     * @return string|null
     */
    public function getImage();
    
    /**
     * @param  string $image
     * @return self
     */
    public function setImage($image);
    
    /**
     * @return int|null
     */
    public function getStatus();
    
    /**
     * @param  int $status
     * @return self
     */
    public function setStatus($status);
    
    /**
     * @return string|null
     */
    public function getAddress();
    
    /**
     * @param  string $address
     * @return self
     */
    public function setAddress($address);
    
    /**
     * @return string|null
     */
    public function getLatitude();
    
    /**
     * @param  string $latitude
     * @return self
     */
    public function setLatitude($latitude);
    
    /**
     * @return string|null
     */
    public function getLongitude();
    
    /**
     * @param  string $longitude
     * @return self
     */
    public function setLongitude($longitude);
    
    /**
     * @return string|null
     */
    public function getPassword();
    
    /**
     * @param  string $password
     * @return self
     */
    public function setPassword($password);
    
    /**
     * @return string|null
     */
    public function getRpToken();
    
    /**
     * @param  string $rpToken
     * @return self
     */
    public function setRpToken($rpToken);
    
    /**
     * @return string
     */
    public function getCreatedAt();
    
    /**
     * @param  string $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt);
    
    /**
     * @return string
     */
    public function getUpdatedAt();
    
    /**
     * @param  string $updatedAt
     * @return self
     */
    public function setUpdatedAt($updatedAt);
    
    /**
     * @return string
     */
    public function getVehicleType();
    
    /**
     * @param  string $vehicleType
     * @return self
     */
    public function setVehicleType($vehicleType);
    
    /**
     * @return string|null
     */
    public function getMobileNumber();
    
    /**
     * @param  string $mobileNumber
     * @return self
     */
    public function setMobileNumber($mobileNumber);
    
    /**
     * @return string|null
     */
    public function getVehicleNumber();
    
    /**
     * @param  string $vehicleNumber
     * @return self
     */
    public function setVehicleNumber($vehicleNumber);
    
    /**
     * @return string
     */
    public function getRpTokenCreatedAt();
    
    /**
     * @param  string $rpTokenCreatedAt
     * @return self
     */
    public function setRpTokenCreatedAt($rpTokenCreatedAt);
    
    /**
     * @return int|null
     */
    public function getAvailabilityStatus();
    
    /**
     * @param  int $availabilityStatus
     * @return self
     */
    public function setAvailabilityStatus($availabilityStatus);
}
