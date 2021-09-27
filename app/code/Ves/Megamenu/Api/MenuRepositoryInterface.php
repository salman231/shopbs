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

interface MenuRepositoryInterface
{


    /**
     * Save Menu data
     *
     * @param \Ves\Megamenu\Api\Data\MenuInterface $menu
     * @return \Ves\Megamenu\Api\Data\MenuInterface
     * @throws CouldNotSaveException
     */
    public function save(\Ves\Megamenu\Api\Data\MenuInterface $menu);

    /**
     * Revert Menu
     * @param string $menuId
     * @param int $revision
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return string
     */
    public function revert($menuId, $revision);

    /**
     * Retrieve Menu
     * @param string $menuId
     * @return \Ves\Megamenu\Api\Data\MenuInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($menuId);

    /**
     * Retrieve Menu matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ves\Megamenu\Api\Data\MenuSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Menu
     * @param \Ves\Megamenu\Api\Data\MenuInterface $menu
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ves\Megamenu\Api\Data\MenuInterface $menu
    );

    /**
     * Delete Menu by ID
     * @param string $menuId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($menuId);
}
