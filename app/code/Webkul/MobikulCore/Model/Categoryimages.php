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
use Webkul\MobikulCore\Api\Data\CategoryimagesInterface;

/**
 * Class Categoryimages model
 */
class Categoryimages extends AbstractModel implements CategoryimagesInterface, IdentityInterface
{
    const NOROUTE_ID = "no-route";
    const CACHE_TAG = "mobikul_categoryimages";
    protected $_cacheTag = "mobikul_categoryimages";
    protected $_eventPrefix = "mobikul_categoryimages";

    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Categoryimages::class);
    }

    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteCategoryimages();
        }
        return parent::load($id, $field);
    }

    public function noRouteCategoryimages()
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

    public function getIcon()
    {
        return parent::getData(self::ICON);
    }

    public function setIcon($icon)
    {
        return $this->setData(self::ICON, $icon);
    }

    public function getBanner()
    {
        return parent::getData(self::BANNER);
    }

    public function setBanner($banner)
    {
        return $this->setData(self::BANNER, $banner);
    }

    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }

    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function getCategoryId()
    {
        return parent::getData(self::CATEGORY_ID);
    }

    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    public function getCategoryName()
    {
        return parent::getData(self::CATEGORY_NAME);
    }

    public function setCategoryName($categoryName)
    {
        return $this->setData(self::CATEGORY_NAME, $categoryName);
    }
}
