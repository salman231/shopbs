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

namespace Mageplaza\DailyDeal\Api;

/**
 * Interface DealRepositoryInterface
 * @package Mageplaza\DailyDeal\Api
 */
interface DealRepositoryInterface
{
    /**
     * Get all deals
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     *
     * @return \Mageplaza\DailyDeal\Api\Data\DealSearchResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllDeals(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null);

    /**
     * @param string $ruleId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($ruleId);

    /**
     * @param \Mageplaza\DailyDeal\Api\Data\DailyDealInterface $deal
     *
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function add($deal);

    /**
     * Get deal by id
     * @param string $id
     *
     * @return \Mageplaza\DailyDeal\Api\Data\DailyDealInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Get deal by product sku
     * @param string $sku
     *
     * @return \Mageplaza\DailyDeal\Api\Data\DailyDealInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByProductSku($sku);
}
