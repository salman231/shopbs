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
 * Interface CarouselimageInterface
 */
interface CarouselimageInterface
{
    const ID = "id";
    const TYPE = "type";
    const TITLE = "title";
    const STATUS = "status";
    const FILENAME = "filename";
    const PRO_CAT_ID = "pro_cat_id";

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
     * @return integer integer|bool
     */
    public function getStatus();

    /**
     * Function setStatus
     *
     * @param integer $status status
     */
    public function setStatus($status);

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
}
