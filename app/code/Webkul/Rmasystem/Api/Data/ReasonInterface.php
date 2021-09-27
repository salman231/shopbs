<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Api\Data;

interface ReasonInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID            = 'id';
    const REASON        = 'reason';
    const STATUS        = 'status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Rma Reason
     *
     * @return string
     */
    public function getReason();

    /**
     * Get Status
     *
     * @return boolen
     */
    public function getStatus();
    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Rmasystem\Api\Data\ReasonInterface
     */
    public function setId($id);

    /**
     * Set Rma Reason
     *
     * @param string $reason
     * @return \Webkul\Rmasystem\Api\Data\ReasonInterface
     */
    public function setReason($reason);

    /**
     * Set Status
     *
     * @param int $status
     * @return \Ashsmith\Blog\Api\Data\ReasonInterface
     */
    public function setStatus($status);
}
