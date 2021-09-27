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

interface GetMenuByAliasInterface
{
    /**
     * Load megamenu profile by alias.
     *
     * @param string $alias
     * @param string $storeCode
     * @param int $customer_group_id
     * @param bool $is_mobile_menu
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Ves\Megamenu\Api\Data\MenuInterface | null
     * @since 103.0.0
     */
    public function execute(string $alias, string $storeCode = 'all', int $customer_group_id = 0, bool $is_mobile_menu = false) : \Ves\Megamenu\Api\Data\MenuInterface;
}
