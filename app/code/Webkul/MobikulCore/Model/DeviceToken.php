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
use Webkul\MobikulCore\Api\Data\DeviceTokenInterface;

/**
 * Class DeviceToken model
 */
class DeviceToken extends \Magento\Framework\Model\AbstractModel implements DeviceTokenInterface, IdentityInterface
{
    const NOROUTE_ID = "no-route";
    const CACHE_TAG = "mobikul_devicetoken";
    protected $_cacheTag = "mobikul_devicetoken";
    protected $_eventPrefix = "mobikul_devicetoken";

    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\DeviceToken::class);
    }

    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteProduct();
        }
        return parent::load($id, $field);
    }

    public function noRouteProduct()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . "_" . $this->getId()];
    }

    public function getId()
    {
        return parent::getData(self::ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    public function getToken()
    {
        return parent::getData(self::TOKEN);
    }

    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }
}
