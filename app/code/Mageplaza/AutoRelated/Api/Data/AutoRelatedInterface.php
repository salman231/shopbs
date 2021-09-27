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
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Api\Data;

/**
 * Interface AutoRelatedInterface
 * @package Mageplaza\AutoRelated\Api\Data
 */
interface AutoRelatedInterface
{

    const RULE_ID                        = 'rule_id';
    const NAME                           = 'name';
    const BLOCK_TYPE                     = 'block_type';
    const FROM_DATE                      = 'from_date';
    const TO_DATE                        = 'to_date';
    const IS_ACTIVE                      = 'is_active';
    const SORT_ORDER                     = 'sort_order';
    const PARENT_ID                      = 'parent_id';
    const IMPRESSION                     = 'impression';
    const CLICK                          = 'click';
    const LOCATION                       = 'location';
    const BLOCK_NAME                     = 'block_name';
    const LIMIT_NUMBER                   = 'limit_number';
    const DISPLAY_OUT_OF_STOCK           = 'display_out_of_stock';
    const PRODUCT_LAYOUT                 = 'product_layout';
    const SORT_ORDER_DIRECTION           = 'sort_order_direction';
    const DISPLAY_ADDITIONAL             = 'display_additional';
    const ADD_RUC_PRODUCT                = 'add_ruc_product';
    const PRODUCT_NOT_DISPLAYED          = 'product_not_displayed';
    const TOTAL_IMPRESSION               = 'total_impression';
    const TOTAL_CLICK                    = 'total_click';
    const CREATED_AT                     = 'created_at';
    const UPDATED_AT                     = 'updated_at';
    const DISPLAY_MODE                   = 'display_mode';
    const CATEGORY_CONDITIONS_SERIALIZED = 'category_conditions_serialized';

    /**
     * @return int
     */
    public function getRuleId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setRuleId($value);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setName($value);

    /**
     * @return string
     */
    public function getBlockType();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBlockType($value);

    /**
     * @return string
     */
    public function getFromDate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFromDate($value);

    /**
     * @return string
     */
    public function getToDate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setToDate($value);

    /**
     * @return int
     */
    public function getIsActive();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setIsActive($value);

    /**
     * Returns rule condition
     *
     * @return \Magento\CatalogRule\Api\Data\ConditionInterface|null
     */
    public function getRuleConditions();

    /**
     * @param \Magento\CatalogRule\Api\Data\ConditionInterface $condition
     * @return $this
     */
    public function setRuleConditions($condition);

    /**
     * Returns rule action
     *
     * @return \Magento\CatalogRule\Api\Data\ConditionInterface|null
     */
    public function getRuleActions();

    /**
     * @param \Magento\CatalogRule\Api\Data\ConditionInterface $condition
     *
     * @return $this
     */
    public function setRuleActions($condition);

    /**
     * @return string
     */
    public function getCategoryConditions();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCategoryConditions($value);

    /**
     * Get products which are matched by rule
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getMatchingProducts();

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface[] $products
     *
     * @return $this
     */
    public function setMatchingProducts($products);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setSortOrder($value);

    /**
     * @return int
     */
    public function getParentId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setParentId($value);

    /**
     * @return int
     */
    public function getImpression();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setImpression($value);

    /**
     * @return int
     */
    public function getClick();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setClick($value);

    /**
     * @return string
     */
    public function getLocation();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLocation($value);

    /**
     * @return string
     */
    public function getBlockName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBlockName($value);

    /**
     * @return int
     */
    public function getLimitNumber();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setLimitNumber($value);

    /**
     * @return int
     */
    public function getDisplayOutOfStock();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setDisplayOutOfStock($value);

    /**
     * @return int
     */
    public function getProductLayout();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setProductLayout($value);

    /**
     * @return int
     */
    public function getSortOrderDirection();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setSortOrderDirection($value);

    /**
     * @return string
     */
    public function getDisplayAdditional();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDisplayAdditional($value);

    /**
     * @return string
     */
    public function getAddRucProduct();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAddRucProduct($value);

    /**
     * @return string
     */
    public function getProductNotDisplayed();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setProductNotDisplayed($value);

    /**
     * @return int
     */
    public function getTotalImpression();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setTotalImpression($value);

    /**
     * @return int
     */
    public function getTotalClick();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setTotalClick($value);

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
     * @return int
     */
    public function getDisplayMode();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setDisplayMode($value);
}
