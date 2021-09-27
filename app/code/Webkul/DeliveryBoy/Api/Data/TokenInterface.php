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

interface TokenInterface
{
    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = "id";
    const OS = "os";
    const IS_ADMIN = "is_admin";
    const TOKEN = "token";
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
    public function getOs();

    /**
     * @param string $os
     * @return self
     */
    public function setOs($os);

    /**
     * @return string|null
     */
    public function getToken();

    /**
     * @param string $token
     * @return self
     */
    public function setToken($token);

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
