<?php
namespace Webkul\Rmasystem\Api\Data;

interface ShippinglabelInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID            = 'id';
    const FILENAME      = 'filename';
    const TITLE         = 'titel';
    const PRICE         = 'price';
    const STATUS        = 'status';

   /**
    * Get ID
    *
    * @return int|null
    */
    public function getId();
    /**
     * Get File Name
     *
     * @return string
     */
    public function getFilename();
    /**
     * Get Title Name
     *
     * @return string
     */
    public function getTitle();
    /**
     * Get Price
     *
     * @return string
     */
    public function getPrice();

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
     * Set File Name
     *
     * @return string
     */
    public function setFilename($filename);
    /**
     * Set Title Name
     *
     * @return string
     */
    public function setTitle($title);
    /**
     * Set Price
     *
     * @return string
     */
    public function setPrice($price);

     /**
      * Get Status
      *
      * @return boolen
      */
    public function setStatus($status);
}
