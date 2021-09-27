<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\DailyDeal\Model;

use Magento\Framework\Model\AbstractModel;
use Mageplaza\DailyDeal\Api\Data\DailyDealInterface;

/**
 * Class Deal
 * @package Mageplaza\DailyDeal\Model
 */
class Deal extends AbstractModel implements DailyDealInterface
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_dailydeal_deal';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_dailydeal_deal';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_dailydeal_deal';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Deal::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function getDealId()
    {
        return $this->getData(self::DEAL_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setDealId($value)
    {
        return $this->setData(self::DEAL_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($value)
    {
        return $this->setData(self::PRODUCT_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductName($value)
    {
        return $this->setData(self::PRODUCT_NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductSku()
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductSku($value)
    {
        return $this->setData(self::PRODUCT_SKU, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsFeatured()
    {
        return $this->getData(self::IS_FEATURED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsFeatured($value)
    {
        return $this->setData(self::IS_FEATURED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDealPrice()
    {
        return $this->getData(self::DEAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDealPrice($value)
    {
        return $this->setData(self::DEAL_PRICE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDealQty()
    {
        return $this->getData(self::DEAL_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setDealQty($value)
    {
        return $this->setData(self::DEAL_QTY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getSaleQty()
    {
        return $this->getData(self::SALE_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setSaleQty($value)
    {
        return $this->setData(self::SALE_QTY, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreIds()
    {
        return $this->getData(self::STORE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreIds($value)
    {
        return $this->setData(self::STORE_IDS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDateFrom()
    {
        return $this->getData(self::DATE_FROM);
    }

    /**
     * {@inheritdoc}
     */
    public function setDateFrom($value)
    {
        return $this->setData(self::DATE_FROM, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDateTo()
    {
        return $this->getData(self::DATE_TO);
    }

    /**
     * {@inheritdoc}
     */
    public function setDateTo($value)
    {
        return $this->setData(self::DATE_TO, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountLabel()
    {
        return $this->getData(self::DISCOUNT_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountLabel($label)
    {
        return $this->setData(self::DISCOUNT_LABEL, $label);
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingTime()
    {
        return $this->getData(self::REMAINING_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setRemainingTime($time)
    {
        return $this->setData(self::REMAINING_TIME, $time);
    }
}
