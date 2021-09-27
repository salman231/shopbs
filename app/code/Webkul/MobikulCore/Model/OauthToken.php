<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Webkul\MobikulCore\Api\Data\OauthTokenInterface;

/**
 * Class OauthToken model
 */
class OauthToken extends \Magento\Framework\Model\AbstractModel implements OauthTokenInterface, IdentityInterface
{
    const NOROUTE_ENTITY_ID = "no-route";
    const CACHE_TAG = "mobikul_oauth_token";
    protected $_cacheTag = "mobikul_oauth_token";
    protected $_eventPrefix = "mobikul_oauth_token";

    /**
     * Constructor function for class OauthToken
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\OauthToken::class);
    }

    /**
     * Function to get all identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . "_" . $this->getId()];
    }

    /**
     * Function to get Id
     *
     * @return integer
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Function to set Id
     *
     * @param integer $id token id
     *
     * @return void
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Function to get Token
     *
     * @return string
     */
    public function getToken()
    {
        return parent::getData(self::TOKEN);
    }

    /**
     * Function to set Token
     *
     * @param string $token token
     *
     * @return void
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * Function to get Secret
     *
     * @return string
     */
    public function getSecret()
    {
        return parent::getData(self::SECRET);
    }

    /**
     * Function to secret
     *
     * @param string $secret secret key
     *
     * @return void
     */
    public function setSecret($secret)
    {
        return $this->setData(self::SECRET, $secret);
    }

    /**
     * Get token by customer id
     *
     * @param int $customerId customer id
     *
     * @return $this
     */
    public function loadByCustomerId($customerId)
    {
        return $this->load($customerId, "customer_id");
    }

    /**
     * Load token data by token
     *
     * @param string $token customer token
     *
     * @return object
     */
    public function loadByToken($token)
    {
        return $this->load($token, "token");
    }

    /**
     * Function to Get value of CreatedAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Function to set created at
     *
     * @param string $createdAt time at which the token is created
     *
     * @return void
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Function to get CustomerId
     *
     * @return integer
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * Function to set customer Id
     *
     * @param integer $customerId customer Id
     *
     * @return void
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }
}
