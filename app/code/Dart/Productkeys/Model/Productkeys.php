<?php
/**
 * Productkeys Productkeys Model.
 * @category  Dart
 * @package   Dart_Productkeys
 *
 */
namespace Dart\Productkeys\Model;

use Magento\Framework\Model\AbstractModel;
use Dart\Productkeys\Api\Data\ProductkeysInterface;

class Productkeys extends AbstractModel implements ProductkeysInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'dart_productkeys';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Dart\Productkeys\Model\ResourceModel\Productkeys');
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($Id)
    {
        return $this->setData(self::ID, $Id);
    }

    /**
     * Get Sku.
     *
     * @return varchar
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * Set Sku.
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Get Type.
     *
     * @return varchar
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set Type.
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get ProductKey.
     *
     * @return varchar
     */
    public function getProductKey()
    {
        return $this->getData(self::PRODUCT_KEY);
    }

    /**
     * Set ProductKey.
     */
    public function setProductKey($productkey)
    {
        return $this->setData(self::PRODUCT_KEY, $productkey);
    }

    /**
     * Get Status.
     *
     * @return varchar
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status.
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Order Inc Id.
     *
     * @return varchar
     */
    public function getOrderincId()
    {
        return $this->getData(self::ORDERINC_ID);
    }

    /**
     * Set Order Inc Id.
     */
    public function setOrderincId($orderincId)
    {
        return $this->setData(self::ORDERINC_ID, $orderincId);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get UpdatedAt.
     *
     * @return varchar
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set UpdatedAt.
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
