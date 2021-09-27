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
 * Interface OauthTokenInterface
 */
interface OauthTokenInterface
{
    const TOKEN = "token";
    const SECRET  = "secret";
    const ENTITY_ID = "entity_id";
    const CREATED_AT = "created_at";
    const CUSTOMER_ID = "customer_id";

    /**
     * Function to get Id
     *
     * @return string
     */
    public function getId();

    /**
     * Function to set id
     *
     * @param string $id id
     *
     * @return void
     */
    public function setId($id);

    /**
     * Function to get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Function to set createdAt
     *
     * @param string $createdAt createdAt
     *
     * @return void
     */
    public function setCreatedAt($createdAt);

    /**
     * Function to get Token
     *
     * @return string
     */
    public function getToken();

    /**
     * Function to set token
     *
     * @param string $token token
     *
     * @return void
     */
    public function setToken($token);

    /**
     * Function to get CustomerId
     *
     * @return integer
     */
    public function getCustomerId();

    /**
     * Function to set customerId
     *
     * @param string $customerId customerId
     *
     * @return void
     */
    public function setCustomerId($customerId);

    /**
     * Function to get Secret
     *
     * @return string
     */
    public function getSecret();

    /**
     * Function to set secret
     *
     * @param string $secret secret
     *
     * @return void
     */
    public function setSecret($secret);
}
