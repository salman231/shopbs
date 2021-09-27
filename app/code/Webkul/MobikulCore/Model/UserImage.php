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

use Webkul\MobikulCore\Api\Data\UserImageInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class UserImage model
 */
class UserImage extends \Magento\Framework\Model\AbstractModel implements UserImageInterface, IdentityInterface
{
    const NOROUTE_ENTITY_ID = "no-route";
    const CACHE_TAG = "mobikul_userimage";
    protected $_cacheTag = "mobikul_userimage";
    protected $_eventPrefix = "mobikul_userimage";

    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\UserImage::class);
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
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
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

    public function getBanner()
    {
        return parent::getData(self::BANNER);
    }

    public function setBanner($banner)
    {
        return $this->setData(self::BANNER, $banner);
    }

    public function getProfile()
    {
        return parent::getData(self::PROFILE);
    }

    public function setProfile($profile)
    {
        return $this->setData(self::PROFILE, $profile);
    }

    public function getIsSocial()
    {
        return parent::getData(self::IS_SOCIAL);
    }

    public function setIsSocial($isSocial)
    {
        return $this->setData(self::IS_SOCIAL, $isSocial);
    }

    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function getCreatedTime()
    {
        return parent::getData(self::CREATED_TIME);
    }

    public function setCreatedTime($createdAt)
    {
        return $this->setData(self::CREATED_TIME, $createdAt);
    }
}
