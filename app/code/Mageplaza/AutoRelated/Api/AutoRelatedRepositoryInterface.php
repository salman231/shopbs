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
 * @package     Mageplaza_AutoRelated
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AutoRelated\Api;

/**
 * Interface AutoRelatedRepositoryInterface
 * @package Mageplaza\AutoRelated\Api
 */
interface AutoRelatedRepositoryInterface
{
    /**
     * Get rules on product page
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $productSearchCriteria
     * @param int|null $storeId
     * @param int|null $customerGroup
     *
     * @return \Mageplaza\AutoRelated\Api\Data\AutoRelatedSearchResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRuleProductPage(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null,
        \Magento\Framework\Api\SearchCriteriaInterface $productSearchCriteria = null,
        $storeId = null,
        $customerGroup = null
    );

    /**
     * Get rules on category page
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $productSearchCriteria
     * @param int|null $storeId
     * @param int|null $customerGroup
     *
     * @return \Mageplaza\AutoRelated\Api\Data\AutoRelatedSearchResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRuleCategoryPage(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null,
        \Magento\Framework\Api\SearchCriteriaInterface $productSearchCriteria = null,
        $storeId = null,
        $customerGroup = null
    );

    /**
     * Get rules on shopping cart page
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $productSearchCriteria
     * @param int|null $storeId
     * @param int|null $customerGroup
     *
     * @return \Mageplaza\AutoRelated\Api\Data\AutoRelatedSearchResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRuleCartPage(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null,
        \Magento\Framework\Api\SearchCriteriaInterface $productSearchCriteria = null,
        $storeId = null,
        $customerGroup = null
    );

    /**
     * Get rules on osc page
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $productSearchCriteria
     * @param int|null $storeId
     * @param int|null $customerGroup
     *
     * @return \Mageplaza\AutoRelated\Api\Data\AutoRelatedSearchResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRuleOSCPage(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null,
        \Magento\Framework\Api\SearchCriteriaInterface $productSearchCriteria = null,
        $storeId = null,
        $customerGroup = null
    );
}
