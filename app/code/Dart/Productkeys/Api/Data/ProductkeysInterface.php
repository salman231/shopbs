<?php
/**
 * Dart_ProductKeys Productkeys Interface.
 *
 * @category    Dart
 *
 */
namespace Dart\Productkeys\Api\Data;

interface ProductkeysInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'id';
    const SKU = 'sku';
    const TYPE = 'type';
    const PRODUCT_KEY = 'product_key';
    const STATUS = 'status';
    const ORDERINC_ID = 'orderinc_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get Id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set Id.
     */
    public function setId($Id);

    /**
     * Get Sku.
     *
     * @return varchar
     */
    public function getSku();

    /**
     * Set Sku.
     */
    public function setSku($sku);

    /**
     * Get Type.
     *
     * @return varchar
     */
    public function getType();

    /**
     * Set Type.
     */
    public function setType($type);

    /**
     * Get Productkey.
     *
     * @return varchar
     */
    public function getProductKey();

    /**
     * Set Productkey.
     */
    public function setProductKey($productkey);

    /**
     * Get Status.
     *
     * @return varchar
     */
    public function getStatus();

    /**
     * Set Status.
     */
    public function setStatus($status);

    /**
     * Get Order Inc Id.
     *
     * @return varchar
     */
    public function getOrderincId();

    /**
     * Set Order Inc Id.
     */
    public function setOrderincId($orderincId);

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt();

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt);

    /**
     * Get UpdatedAt.
     *
     * @return varchar
     */
    public function getUpdatedAt();

    /**
     * Set UpdatedAt.
     */
    public function setUpdatedAt($updatedAt);
}
