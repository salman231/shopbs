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

class Preview extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Ves\Megamenu\Model\Menu
     */
    protected $_menu;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Ves\Megamenu\Helper\MobileDetect
     */
    protected $_mobileDetect;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context            
     * @param \Magento\Framework\Registry                      $registry           
     * @param \Ves\Megamenu\Helper\Data                        $helper             
     * @param \Ves\Megamenu\Model\Menu                         $menu               
     * @param \Ves\Megamenu\Helper\MobileDetect                $mobileDetectHelper 
     * @param array                                            $data               
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Megamenu\Helper\Data $helper,
        \Ves\Megamenu\Model\Menu $menu,
        \Ves\Megamenu\Helper\MobileDetect $mobileDetectHelper,
        array $data = []
        ) {
        parent::__construct($context);
        $this->_helper       = $helper;
        $this->_menu         = $menu;
        $this->_mobileDetect = $mobileDetectHelper;
        $this->_coreRegistry = $registry;
    }


    public function getMobileTemplateHtml($menuAlias)
    {
        $html = '';
        if($menuAlias){
            $html = $this->getLayout()->createBlock('Ves\Megamenu\Block\MobileMenu')->setData('alias', $menuAlias)->toHtml();
        }
        return $html;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities() {
        return [\Ves\Megamenu\Model\Menu::CACHE_TAG . '_' . $this->getBlockId()];
    }

    public function getMobileDetect() {
        return $this->_mobileDetect;
    }

    public function _toHtml() {
        if(!$this->getTemplate()) {
            $this->setTemplate("Ves_Megamenu::widget/menu.phtml");
        }

        $this->setData("menu", $this->getMenu()); 
        return parent::_toHtml();
    }

    public function getMenu() {
        $menu = $this->_coreRegistry->registry('current_menu');
        return $menu;
    }
}