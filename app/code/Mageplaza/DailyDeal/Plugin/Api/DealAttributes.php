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

namespace Mageplaza\DailyDeal\Plugin\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\DailyDeal\Block\Product\View\Label;
use Mageplaza\DailyDeal\Helper\Data;
use Mageplaza\DailyDeal\Model\Deal;

/**
 * Class DealAttributes
 * @package Mageplaza\DailyDeal\Plugin\Api
 */
class DealAttributes
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Label
     */
    protected $block;

    /**
     * DealAttributes constructor.
     *
     * @param Data $helperData
     * @param Label $dealBlock
     */
    public function __construct(
        Data $helperData,
        Label $dealBlock
    ) {
        $this->helperData = $helperData;
        $this->block      = $dealBlock;
    }

    /**
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $entity
     *
     * @return ProductInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function afterGet(
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    ) {
        if (!$this->helperData->isEnabled()) {
            return $entity;
        }
        $this->addDealToProduct($entity);

        return $entity;
    }

    /**
     * @param ProductRepositoryInterface $subject
     * @param SearchResults $searchCriteria
     *
     * @return SearchResults
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(Unused)
     */
    public function afterGetList(
        ProductRepositoryInterface $subject,
        SearchResults $searchCriteria
    ) {
        if (!$this->helperData->isEnabled()) {
            return $searchCriteria;
        }

        /** @var ProductInterface $entity */
        foreach ($searchCriteria->getItems() as $entity) {
            $this->addDealToProduct($entity);
        }

        return $searchCriteria;
    }

    /**
     * @param ProductInterface $entity
     *
     * @return bool|ProductInterface
     * @throws NoSuchEntityException
     */
    public function addDealToProduct($entity)
    {
        $productId = $entity->getId();
        /** @var Deal $dealData */
        $dealData = $this->helperData->getProductDeal($productId);
        if (!$dealData->getId()) {
            return false;
        }

        $percent        = $this->helperData->checkDealConfigurableProduct($productId)
            ? $this->block->getMaxPercent($productId)
            : $this->block->getPercentDiscount($productId);
        $discountLabels = $this->block->getLabel($percent);
        $dealData->setDiscountLabel($discountLabels);
        $dealData->setRemainingTime($this->helperData->getRemainTime($dealData));

        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes !== null) {
            $extensionAttributes->setMpDailydeal($dealData);
        }

        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }
}
