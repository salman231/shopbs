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

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Webkul\MobikulCore\Api\Data\NotificationInterface;

/**
 * Class NotificationImage model
 */
class Notification extends AbstractModel implements NotificationInterface, IdentityInterface
{
    const NOROUTE_ID = "no-route";
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    const TYPE_PRODUCT = "product";
    const TYPE_CATEGORY = "category";
    const TYPE_OTHERS = "others";
    const TYPE_CUSTOM = "custom";
    const CACHE_TAG = "mobikul_notification";
    protected $_cacheTag = "mobikul_notification";
    protected $_eventPrefix = "mobikul_notification";

    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Notification::class);
    }

    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteNotification();
        }
        return parent::load($id, $field);
    }

    public function noRouteNotification()
    {
        return $this->load(self::NOROUTE_ID, $this->getIdFieldName());
    }

    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED  => __("Enabled"),
            self::STATUS_DISABLED => __("Disabled")
        ];
    }

    public function getAvailableTypes()
    {
        return [
            self::TYPE_PRODUCT  => __("Product"),
            self::TYPE_CATEGORY => __("Category"),
            self::TYPE_OTHERS   => __("Others"),
            self::TYPE_CUSTOM   => __("Custom Collection")
        ];
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

    public function getFilename()
    {
        return parent::getData(self::FILENAME);
    }

    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME, $filename);
    }

    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    public function getContent()
    {
        return parent::getData(self::CONTENT);
    }

    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    public function getProCatId()
    {
        return parent::getData(self::PRO_CAT_ID);
    }

    public function setProCatId($proCatId)
    {
        return $this->setData(self::PRO_CAT_ID, $proCatId);
    }

    public function getStoreId()
    {
        return parent::getData(self::STORE_ID);
    }

    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getSortOrder()
    {
        return parent::getData(self::SORT_ORDER);
    }

    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    public function getCreatedTime()
    {
        return parent::getData(self::CREATED_TIME);
    }

    public function setCreatedTime($createdAt)
    {
        return $this->setData(self::CREATED_TIME, $createdAt);
    }

    public function getUpdateTime()
    {
        return parent::getData(self::UPDATE_TIME);
    }

    public function setUpdateTime($updatedAt)
    {
        return $this->setData(self::UPDATE_TIME, $updatedAt);
    }
}
