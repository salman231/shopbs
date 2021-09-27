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

interface ConversationInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                = 'id';
    const RMA_ID            = 'rma_id';
    const MESSAGE           = 'message';
    const CREATED_AT        = 'created_at';
    const SENDER            = 'sender';

    /**
     * Get Id
     * @return int
     */
    public function getId();

    /**
     * get rma id
     * @return int
     */
    public function getRmaId();

    /**
     * get Message
     * @return string
     */
    public function getMessage();

    /**
     * get creation time.
     * @return string
     */
    public function getCreatedAt();

    /**
     * get Sender
     * @return string
     */
    public function getSender();

    /**
     * set Id
     * @return \Webkul\Rmasystem\Api\Data\ConversationInterface
     */
    public function setId($id);

    /**
     * set rma id
     * @return \Webkul\Rmasystem\Api\Data\ConversationInterface
     */
    public function setRmaId($rmaId);

    /**
     * set Message
     * @return \Webkul\Rmasystem\Api\Data\ConversationInterface
     */
    public function setMessage($message);

    /**
     * set creation time.
     * @return \Webkul\Rmasystem\Api\Data\ConversationInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * set Sender
     * @return \Webkul\Rmasystem\Api\Data\ConversationInterface
     */
    public function setSender($sender);
}
