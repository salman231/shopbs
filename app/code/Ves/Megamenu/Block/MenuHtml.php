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

namespace Ves\Megamenu\Block;

class MenuHtml extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Ves\Megamenu\Helper\Data
	 */
	protected $_helper;

	/**
	 * @var \Ves\Megamenu\Helper\MobileDetect
	 */
	protected $_mobileDetect;

	/**
	 * @var \Magento\Framework\App\ResourceConnection
	 */
	protected $_resource;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context            
	 * @param \Ves\Megamenu\Helper\Data                        $helper             
	 * @param \Ves\Megamenu\Helper\MobileDetect                $mobileDetectHelper 
	 * @param \Magento\Framework\App\ResourceConnection        $resource           
	 * @param array                                            $data               
	 */
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Ves\Megamenu\Helper\Data $helper,
		\Ves\Megamenu\Helper\MobileDetect $mobileDetectHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		array $data = []
		) {
		parent::__construct($context);
		$this->_helper          = $helper;
		$this->_mobileDetect    = $mobileDetectHelper;
		$this->_resource        = $resource;
	}

	public function _toHtml() {
		$helper = $this->_helper;
		$menu   = $this->getMenu();
		$store  = $this->getStore();
		$html   = '';
		$helper->setStore($store);
		$mobileTemplate = $menu->getMobileTemplate();
		if ($mobileTemplate == 2 && $menu->getMobileMenuAlias() && $this->_mobileDetect->isMobile()) {
			$html = $this->getMobileTemplateHtml($menu->getMobileMenuAlias());
		} else {
			$menuItems  = $menu->getData('menuItems');
			$structure  = json_decode($menu->getStructure(), true);
			$categories = [];
			foreach ($menuItems as $item) {
				if (isset($item['link_type']) && $item['link_type'] == 'category_link' && isset($item['category']) && !in_array($item['category'], $categories)) {
					$categories[] = $item['category'];
				}
			}
			$helper->setMenuCategories($categories);
			if(is_array($structure)){
				foreach ($structure as $k => $v) {
					$itemData = $helper->renderMenuItemData($v, [], $menuItems);
					$html     .= $helper->drawItem($itemData);
				}
			}
		}
		$html = $helper->filter($html);
		return $html;
	}

	public function getMobileTemplateHtml($menuAlias)
	{
		$html = '';
		if($menuAlias){
			$html = $this->getLayout()->createBlock('Ves\Megamenu\Block\MobileMenu')->setData('alias', $menuAlias)->toHtml();
		}
		return $html;
	}
}