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
 * Interface CarouselInterface
 */
interface CarouselInterface
{
    const ID = "id";
    const TYPE = "type";
    const TITLE = "title";
    const STATUS = "status";
    const IMAGES = "images";
    const FILENAME = "filename";
    const STORE_ID = "store_id";
    const IMAGE_IDS = "image_ids";
    const SORT_ORDER = "sort_order";
    const COLOR_CODE = "color_code";
    const PRODUCT_IDS = "product_ids";

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
     * Function getType
     *
     * @return integer
     */
    public function getType();

    /**
     * Function setType
     *
     * @param integer $type type
     */
    public function setType($type);

    /**
     * Function getTitle
     *
     * @return string
     */
    public function getTitle();

    /**
     * Function setTitle
     *
     * @param string $title title
     */
    public function setTitle($title);

    /**
     * Function getStatus
     *
     * @return integer
     */
    public function getStatus();

    /**
     * Function setStatus
     *
     * @param integer $status status
     */
    public function setStatus($status);

    /**
     * Function getImages
     *
     * @return string
     */
    public function getImages();

    /**
     * Function setImages
     *
     * @param string $images images
     */
    public function setImages($images);

    /**
     * Function getFilename
     *
     * @return string
     */
    public function getFilename();

    /**
     * Function setFilename
     *
     * @param string $fileName fileName
     */
    public function setFilename($fileName);

    /**
     * Function getImageIds
     *
     * @return integer
     */
    public function getImageIds();

    /**
     * Function setImageIds
     *
     * @param integer $imageIds imageIds
     */
    public function setImageIds($imageIds);

    /**
     * Function getStoreId
     *
     * @param integer
     */
    public function getStoreId();

    /**
     * Function setStoreId
     *
     * @param integer $storeId storeId
     */
    public function setStoreId($storeId);

    /**
     * Function getSortOrder
     *
     * @return integer
     */
    public function getSortOrder();

    /**
     * Function setSortOrder
     *
     * @param integer $sortOrder sortOrder
     */
    public function setSortOrder($sortOrder);

    /**
     * Function getColorCode
     *
     * @return string
     */
    public function getColorCode();

    /**
     * Function setColorCode
     *
     * @param string $colorCode colorCode
     */
    public function setColorCode($colorCode);

    /**
     * Function getProductIds
     *
     * @return integer
     */
    public function getProductIds();

    /**
     * Function setProductIds
     *
     * @param integer $productIds productIds
     */
    public function setProductIds($productIds);
}
