<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2019 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Api;

interface ItemRepositoryInterface
{


    /**
     * Save Menu data
     *
     * @param \Ves\Megamenu\Api\Data\ItemInterface $menuItem
     * @return \Ves\Megamenu\Api\Data\ItemInterface
     * @throws CouldNotSaveException
     */
    public function save(\Ves\Megamenu\Api\Data\ItemInterface $menuItem);

    /**
     * Retrieve Menu Item
     * @param int $menuItemId
     * @param string $storeId
     * @return \Ves\Megamenu\Api\Data\ItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($menuItemId, $storeId);

    /**
     * Retrieve Menu Item matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ves\Megamenu\Api\Data\ItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Menu Item
     * @param \Ves\Megamenu\Api\Data\ItemInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ves\Megamenu\Api\Data\ItemInterface $item
    );

    /**
     * Delete Menu Item by ID
     * @param string $menuItemId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($menuItemId);

    /**
     * Get Menu Items by Menu ID
     * @param int $menuId
     * @param string $storeId
     * @return \Ves\Megamenu\Api\Data\ItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByMenuId($menuId, $storeId);


    /**
     * Backend: Retrieve Menu Item by id
     * @param int $menuItemId
     * @param string $storeId
     * @return \Ves\Megamenu\Api\Data\ItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByIdBackend($menuItemId, $storeId);

    /**
     * Backend: Get Menu Items by Menu ID
     * @param int $menuId
     * @param string $storeId
     * @return \Ves\Megamenu\Api\Data\ItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByMenuIdBackend($menuId, $storeId);
}
