<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterProductReviews
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Api\Data;

/**
 * Interface ReviewInterface
 * @package Mageplaza\BetterProductReviews\Api\Data
 */
interface ReviewInterface
{
    /**
     * Constants used as data array keys
     */
    const REVIEW_ID                  = 'review_id';
    const CREATED_AT                 = 'created_at';
    const ENTITY_ID                  = 'entity_id';
    const ENTITY_PK_VALUE            = 'entity_pk_value';
    const STATUS_ID                  = 'status_id';
    const STORE_ID                   = 'store_id';
    const DETAIL_ID                  = 'detail_id';
    const TITLE                      = 'title';
    const DETAIL                     = 'detail';
    const NICKNAME                   = 'nickname';
    const CUSTOMER_ID                = 'customer_id';
    const MP_BPR_IMAGES              = 'mp_bpr_images';
    const MP_BPR_RECOMMENDED_PRODUCT = 'mp_bpr_recommended_product';
    const MP_BPR_VERIFIED_BUYER      = 'mp_bpr_verified_buyer';
    const MP_BPR_HELPFUL             = 'mp_bpr_helpful';
    const AVG_VALUE                  = 'avg_value';

    const ATTRIBUTES = [
        self::REVIEW_ID,
        self::CREATED_AT,
        self::ENTITY_ID,
        self::ENTITY_PK_VALUE,
        self::STATUS_ID,
        self::DETAIL_ID,
        self::STORE_ID,
        self::NICKNAME,
        self::TITLE,
        self::DETAIL,
        self::CUSTOMER_ID,
        self::MP_BPR_IMAGES,
        self::MP_BPR_RECOMMENDED_PRODUCT,
        self::MP_BPR_VERIFIED_BUYER,
        self::MP_BPR_HELPFUL,
        self::AVG_VALUE
    ];

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @return int|null
     */
    public function getEntityId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setEntityId($id);

    /**
     * @return int|null
     */
    public function getEntityPkValue();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setEntityPkValue($id);

    /**
     * @return int|null
     */
    public function getStatusId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setStatusId($id);

    /**
     * @return int|null
     */
    public function getStoreId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setStoreId($id);

    /**
     * @return int|null
     */
    public function getDetailId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setDetailId($id);

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string|null
     */
    public function getDetail();

    /**
     * @param string $detail
     *
     * @return $this
     */
    public function setDetail($detail);

    /**
     * @return string|null
     */
    public function getNickname();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setNickname($name);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setCustomerId($id);

    /**
     * @return string|null
     */
    public function getMpBprImages();

    /**
     * @param string $image
     *
     * @return $this
     */
    public function setMpBprImages($image);

    /**
     * @return int|null
     */
    public function getMpBprHelpful();

    /**
     * @return string|null
     */
    public function getMpBprRecommendedProduct();

    /**
     * @param string $recommended
     *
     * @return $this
     */
    public function setMpBprRecommendedProduct($recommended);

    /**
     * @return string|null
     */
    public function getMpBprVerifiedBuyer();

    /**
     * @param string $image
     *
     * @return $this
     */
    public function setMpBprVerifiedBuyer($image);

    /**
     * @return string|null
     */
    public function getAvgValue();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAvgValue($value);
}
