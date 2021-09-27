<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Api\Data;

/**
 * MagentoChatSystem customer interface.
 * @api
 */
interface CustomerDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID     = 'entity_id';
    const CUSTOMER_ID   = 'customer_id';
    const UNIQUE_ID     = 'unique_id';
    const CHAT_STATUS   = 'chat_status';
    const IMAGE         = 'image';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get customer unique ID
     *
     * @return string|null
     */
    public function getUniqueId();

    /**
     * Get chat status
     *
     * @return int|null
     */
    public function getChatStatus();

    /**
     * Get chat status
     *
     * @return string|null
     */
    public function getImage();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setId($id);

     /**
      * Set customer ID
      *
      * @param int $id
      * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
      */
    public function setCustomerId($customerId);

    /**
     * Set customer ID
     *
     * @param string $uniqueId
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setUniqueId($uniqueId);

    /**
     * Set chat status
     *
     * @param int $id
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setChatStatus($status);
    /**
     * Set chat status
     *
     * @param string $image
     * @return \Webkul\MagentoChatSystem\Api\Data\CustomerDataInterface
     */
    public function setImage($image);
}
