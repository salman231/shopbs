<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Api\Data;

/**
 * Interface CategoryimagesInterface
 */
interface CategoryimagesInterface
{
    const ID = "id";
    const ICON = "icon";
    const BANNER = "banner";
    const CREATED_AT = "created_at";
    const UPDATED_AT = "updated_at";
    const CATEGORY_ID = "category_id";
    const CATEGORY_NAME = "category_name";

    /**
     * Function getId
     *
     * @return integer
     */
    public function getId();

    /**
     * Function setId
     *
     * @param integer $id id
     */
    public function setId($id);

    /**
     * Function getIcon
     *
     * @return string
     */
    public function getIcon();

    /**
     * Function setIcon
     *
     * @param string $icon icon
     */
    public function setIcon($icon);

    /**
     * Function getBanner
     *
     * @return string
     */
    public function getBanner();

    /**
     * Function setBanner
     *
     * @param string $banner banner
     */
    public function setBanner($banner);

    /**
     * Function getCreatedAt
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Function setCreatedAt
     *
     * @param string $createdAt createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * Function getUpdatedAt
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Function setUpdatedAt
     *
     * @param string $updatedAt updatedAt
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Function getCategoryId
     *
     * @return string
     */
    public function getCategoryId();

    /**
     * Function setCategoryId
     *
     * @param integer $categoryId categoryId
     */
    public function setCategoryId($categoryId);

    /**
     * Function getCategoryName
     *
     * @return string
     */
    public function getCategoryName();

    /**
     * Function setCategoryName
     *
     * @param string $categoryName categoryName
     */
    public function setCategoryName($categoryName);
}
