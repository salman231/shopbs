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
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Api\Data;

/**
 * Interface DailyDealInterface
 * @package Mageplaza\DailyDeal\Api\Data
 */
interface DailyDealInterface
{
    const DEAL_ID      = 'deal_id';
    const PRODUCT_ID   = 'product_id';
    const PRODUCT_NAME = 'product_name';
    const PRODUCT_SKU  = 'product_sku';
    const STATUS       = 'status';
    const IS_FEATURED  = 'is_featured';
    const DEAL_PRICE   = 'deal_price';
    const DEAL_QTY     = 'deal_qty';
    const SALE_QTY     = 'sale_qty';
    const STORE_IDS    = 'store_ids';
    const DATE_FROM    = 'date_from';
    const DATE_TO      = 'date_to';
    const CREATED_AT   = 'created_at';
    const UPDATED_AT   = 'updated_at';
    const DISCOUNT_LABEL = 'discount_label';
    const REMAINING_TIME = 'remaining_time';

    /**
     * @return int
     */
    public function getDealId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setDealId($value);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setProductId($value);

    /**
     * @return string
     */
    public function getProductName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setProductName($value);

    /**
     * @return string
     */
    public function getProductSku();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setProductSku($value);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return int
     */
    public function getIsFeatured();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setIsFeatured($value);

    /**
     * @return float
     */
    public function getDealPrice();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setDealPrice($value);

    /**
     * @return int
     */
    public function getDealQty();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setDealQty($value);

    /**
     * @return int
     */
    public function getSaleQty();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setSaleQty($value);

    /**
     * @return string
     */
    public function getStoreIds();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStoreIds($value);

    /**
     * @return string
     */
    public function getDateFrom();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDateFrom($value);

    /**
     * @return string
     */
    public function getDateTo();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDateTo($value);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCreatedAt($value);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setUpdatedAt($value);

    /**
     * @param string $label
     *
     * @return mixed
     */
    public function setDiscountLabel($label);

    /**
     * @return string
     */
    public function getDiscountLabel();

    /**
     * @param string $time
     *
     * @return mixed
     */
    public function setRemainingTime($time);

    /**
     * @return string
     */
    public function getRemainingTime();
}
