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
 * Interface NotificationInterface
 */
interface NotificationInterface
{
    const ID = "id";
    const TYPE = "type";
    const TITLE = "title";
    const STATUS = "status";
    const CONTENT = "content";
    const FILENAME = "filename";
    const STORE_ID = "store_id";
    const PRO_CAT_ID = "pro_cat_id";
    const SORT_ORDER = "sort_order";
    const UPDATE_TIME = "update_time";
    const CREATED_TIME = "created_time";

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
     * Function getContent
     *
     * @return string
     */
    public function getContent();

    /**
     * Function setContent
     *
     * @param string $content content
     */
    public function setContent($content);

    /**
     * Function getFilename
     *
     * @return string
     */
    public function getFilename();

    /**
     * Function setFilename
     *
     * @param string $filename filename
     */
    public function setFilename($filename);

    /**
     * Function getStoreId
     *
     * @return integer
     */
    public function getStoreId();

    /**
     * Function setStoreId
     *
     * @param integer $storeId storeId
     */
    public function setStoreId($storeId);

    /**
     * Function getProCatId
     *
     * @return integer
     */
    public function getProCatId();

    /**
     * Function setProCatId
     *
     * @param integer $proCatId proCatId
     */
    public function setProCatId($proCatId);

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
     * Function getUpdateTime
     *
     * @return string
     */
    public function getUpdateTime();

    /**
     * Function setUpdateTime
     *
     * @param string $updatedAt updatedAt
     */
    public function setUpdateTime($updatedAt);

    /**
     * Function getCreatedTime
     *
     * @return string
     */
    public function getCreatedTime();

    /**
     * Function setCreatedTime
     *
     * @param string $createdAt createdAt
     */
    public function setCreatedTime($createdAt);
}
