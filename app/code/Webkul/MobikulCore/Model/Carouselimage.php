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
use Webkul\MobikulCore\Api\Data\CarouselimageInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Carouselimage extends AbstractModel implements CarouselimageInterface, IdentityInterface
{

    const CACHE_TAG = "mobikul_carouselimage";
    const NOROUTE_ID = "no-route";
    const TYPE_PRODUCT = "product";
    const TYPE_CATEGORY = "category";
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    protected $_cacheTag = "mobikul_carouselimage";
    protected $_eventPrefix = "mobikul_carouselimage";

    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Carouselimage::class);
    }

    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteCarouselimage();
        }
        return parent::load($id, $field);
    }

    public function noRouteCarouselimage()
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
            self::TYPE_CATEGORY => __("Category")
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

    public function getType()
    {
        return parent::getData(self::TYPE);
    }

    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }
    
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getFilename()
    {
        return parent::getData(self::FILENAME);
    }

    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME, $filename);
    }

    public function getProCatId()
    {
        return parent::getData(self::PRO_CAT_ID);
    }

    public function setProCatId($proCatId)
    {
        return $this->setData(self::PRO_CAT_ID, $proCatId);
    }
}
