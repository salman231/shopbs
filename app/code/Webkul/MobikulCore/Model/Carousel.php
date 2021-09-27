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
use Webkul\MobikulCore\Api\Data\CarouselInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Carousel model
 */
class Carousel extends AbstractModel implements CarouselInterface, IdentityInterface
{
    const CACHE_TAG = "mobikul_carousel";
    const NOROUTE_ID = "no-route";
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    protected $_cacheTag = "mobikul_carousel";
    protected $_eventPrefix = "mobikul_carousel";

    protected function _construct()
    {
        $this->_init(\Webkul\MobikulCore\Model\ResourceModel\Carousel::class);
    }

    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteCarousel();
        }
        return parent::load($id, $field);
    }

    public function noRouteCarousel()
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

    public function getImages()
    {
        return parent::getData(self::IMAGES);
    }

    public function setImages($images)
    {
        return $this->setData(self::IMAGES, $images);
    }

    public function getStoreId()
    {
        return parent::getData(self::STORE_ID);
    }

    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getFilename()
    {
        return parent::getData(self::FILENAME);
    }

    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME, $filename);
    }

    public function getImageIds()
    {
        return parent::getData(self::IMAGE_IDS);
    }

    public function setImageIds($imageIds)
    {
        return $this->setData(self::IMAGE_IDS, $imageIds);
    }

    public function getColorCode()
    {
        return parent::getData(self::COLOR_CODE);
    }

    public function setColorCode($colorCode)
    {
        return $this->setData(self::COLOR_CODE, $colorCode);
    }

    public function getSortOrder()
    {
        return parent::getData(self::SORT_ORDER);
    }

    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    public function getProductIds()
    {
        return parent::getData(self::PRODUCT_IDS);
    }

    public function setProductIds($productIds)
    {
        return $this->setData(self::PRODUCT_IDS, $productIds);
    }
}
