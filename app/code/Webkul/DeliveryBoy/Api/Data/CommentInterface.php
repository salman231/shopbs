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

interface CommentInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = "id";
    const COMMENT = "comment";
    const SENDER_ID = "sender_id";
    const CREATED_AT = "created_at";
    const COMMENTED_BY = "commented_by";
    const IS_DELIVERYBOY = "is_deliveryboy";
    const ORDER_INCREMENT_ID = "order_increment_id";
    const DELIVERYBOY_ORDER_ID = "deliveryboy_order_id";

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param  int $id
     * @return self
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getComment();

    /**
     * @param  string $comment
     * @return self
     */
    public function setComment($comment);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param  string $createdAt
     * @return self
     */
    public function setCreatedAt($createdAt);

    /**
     * @return int|null
     */
    public function getSenderId();

    /**
     * @param  int $senderId
     * @return self
     */
    public function setSenderId($senderId);

    /**
     * Get isDeliveryBoy flag  0 false 1 true
     *
     * Whether the comment is written by delivery boy
     *
     * @return int|null
     */
    public function getIsDeliveryboy();

    /**
     * Set isDeliveryBoy flag  0 false 1 true
     *
     * Whether the comment is written by delivery boy
     *
     * @param  int
     * @return self
     */
    public function setIsDeliveryboy($isDeliveryboy);

    /**
     * @return int|null
     */
    public function getOrderIncrementId();

    /**
     * @param  int $orderIncrementId
     * @return self
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * @return int|null
     */
    public function getDeliveryboyOrderId();

    /**
     * @param  int $deliveryboyOrderId
     * @return self
     */
    public function setDeliveryboyOrderId($deliveryboyOrderId);
}
