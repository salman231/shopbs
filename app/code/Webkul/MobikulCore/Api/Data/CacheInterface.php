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
 * Interface CacheInterface
 */
interface CacheInterface
{
    const ID = "id";
    const E_TAG = "e_tag";
    const COUNTER = "counter";
    const REQUEST_TAG = "request_tag";

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
     * Function getETag
     *
     * @return integer
     */
    public function getETag();

    /**
     * Function setETag
     *
     * @param string $eTag eTag
     */
    public function setETag($eTag);

    /**
     * Function getCounter
     *
     * @return integer
     */
    public function getCounter();

    /**
     * Function setCounter
     *
     * @param string $counter counter
     */
    public function setCounter($counter);

    /**
     * Function getRequestTag
     *
     * @return integer
     */
    public function getRequestTag();

    /**
     * Function setRequestTag
     *
     * @param integer $requestTag requestTag
     */
    public function setRequestTag($requestTag);
}
