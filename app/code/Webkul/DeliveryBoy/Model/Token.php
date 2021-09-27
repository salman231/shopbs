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
use Webkul\DeliveryBoy\Api\Data\TokenInterface;

class Token extends AbstractModel implements TokenInterface, IdentityInterface
{
    /**
     * Tag to associate cache entries with
     */
    const CACHE_TAG = "expressdelivery_token";
    
    /**
     * Default Id for when id field value is null
     */
    const NOROUTE_ID = "no-route";

    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_token";

    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_token";

    /**
     * Initialize model object
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\DeliveryBoy\Model\ResourceModel\Token::class);
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
     * @return self
     */
    public function noRouteOrder()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
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
    public function getOs()
    {
        return parent::getData(self::OS);
    }

    /**
     * @param string $os
     * @return self
     */
    public function setOs($os)
    {
        return $this->setData(self::OS, $os);
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return parent::getData(self::TOKEN);
    }

    /**
     * @param string $token
     * @return self
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
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
