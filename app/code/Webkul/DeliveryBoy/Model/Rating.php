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

use Magento\Framework\Model\AbstractModel;
use Webkul\DeliveryBoy\Api\Data\RatingInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Rating extends AbstractModel implements RatingInterface, IdentityInterface
{
    const STATUS_APPROVED = 1;
    const STATUS_PENDING = 2;
    const STATUS_NOT_APPROVED = 3;

    /**
     * Tag to associate cache entries with
     */
    const CACHE_TAG = "expressdelivery_rating";

    /**
     * Default Id for when id field value is null
     */
    const NOROUTE_ID = "no-route";
    /**
     * Tag to associate cache entries with
     *
     * @var string
     */
    protected $_cacheTag = "expressdelivery_rating";
    
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = "expressdelivery_rating";

    /**
     * Initialize model object
     *
     * @return self
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\DeliveryBoy\Model\ResourceModel\Rating::class
        );
    }

    /**
     * @param int $id
     * @param string $field
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
    public function getTitle()
    {
        return parent::getData(self::TITLE);
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return float|null
     */
    public function getRating()
    {
        return parent::getData(self::RATING);
    }

    /**
     * @param float $rating
     * @return self
     */
    public function setRating($rating)
    {
        return $this->setData(self::RATING, $rating);
    }

    /**
     * @return string|null
     */
    public function getComment()
    {
        return parent::getData(self::COMMENT);
    }

    /**
     * @param string $comment
     * @return self
     */
    public function setComment($comment)
    {
        return $this->setData(self::COMMENT, $comment);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     * @return self
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
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

    /**
     * @param int $deliveryboyId
     * @return self
     */
    public function getAverageRating($deliveryboyId)
    {
        return $this->getCollection()->addFieldToFilter(
            "status",
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addFieldToFilter(
            "deliveryboy_id",
            $deliveryboyId
        )->addExpressionFieldToSelect(
            "avg_rating",
            "ROUND(AVG({{rating}}), 1)",
            "rating"
        )->getFirstItem()->getAvgRating();
    }
}
