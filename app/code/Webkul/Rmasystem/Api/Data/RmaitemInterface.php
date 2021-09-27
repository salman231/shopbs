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

interface RmaitemInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID            = 'id';
    const RMA_ID        = 'rma_id';
    const ITEM_ID       = 'item_id';
    const REASON_ID     = 'reason_id';
    const QTY           = 'qty';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get RMA ID
     *
     * @return string
     */
    public function getRmaId();

    /**
     * Get Item ID
     *
     * @return string
     */
    public function getItemId();

    /**
     * Get Reason ID
     *
     * @return string
     */
    public function getReasonId();

    /**
     * Get returned quantity
     *
     * @return int|null
     */
    public function getQty();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Rmasystem\Api\Data\RmaitemInterface
     */
    public function setId($id);

    /**
     * Set Item ID
     * @param int $itemId
     * @return int|null
     */
    public function setItemId($itemId);

    /**
     * Set RMA ID
     * @param int $rmaId
     * @return int|null
     */
    public function setRmaId($rmaId);

    /**
     * Set Reason ID
     * @param int $reasonId
     * @return int|null
     */
    public function setReasonId($reasonId);

    /**
     * set return qty
     * @param int $qty
     * @return int|null
     */
    public function setQty($qty);
}
