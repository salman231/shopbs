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
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Model\Store;

use \Magento\Store\Api\StoreCookieManagerInterface;
use \Magento\Store\Model\ScopeInterface;

class StoreResolver extends \Magento\Store\Model\StoreResolver
{
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        StoreCookieManagerInterface $storeCookieManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoresData $storesData,
        \Magento\Store\App\Request\StorePathInfoValidator $storePathInfoValidator,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode = null,
        \Magento\Framework\Registry $registry
    ) {
    	parent::__construct($storeRepository, $storeCookieManager, $request, $storesData, $storePathInfoValidator, $runMode, $scopeCode);
        $this->_coreRegistry = $registry;
    }

    public function getCurrentStoreId()
    {
    	$store = $this->_coreRegistry->registry('menu_store');
    	if ($store) {
    		return $store->getId();
    	}
    	return parent::getCurrentStoreId();
    }
}