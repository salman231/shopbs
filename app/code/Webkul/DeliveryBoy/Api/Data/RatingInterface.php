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

interface RatingInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = "id";
    const TITLE = "title";
    const RATING = "rating";
    const STATUS = "status";
    const COMMENT = "comment";
    const CREATED_AT = "created_at";
    const CUSTOMER_ID = "customer_id";
    const DELIVERYBOY_ID = "deliveryboy_id";

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return self
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string $title
     * @return self
     */
    public function setTitle($title);

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @param string $status
     * @return self
     */
    public function setStatus($status);

    /**
     * @return float|null
     */
    public function getRating();

    /**
     * @param float $rating
     * @return self
     */
    public function setRating($rating);

    /**
     * @return string|null
     */
    public function getComment();

    /**
     * @param string $comment
     * @return self
     */
    public function setComment($comment);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return self
     */
    public function setCustomerId($customerId);

    /**
     * @return int
     */
    public function getDeliveryboyId();

    /**
     * @param int $deliveryboyId
     * @return self
     */
    public function setDeliveryboyId($deliveryboyId);
}
