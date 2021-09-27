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

namespace Ves\Megamenu\Block\Widget;

use Magento\Framework\Serialize\Serializer\Json;
use \Magento\Framework\App\ObjectManager;

class Menu extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Ves\Megamenu\Model\Menu
     */
    protected $_menu;

    /**
     * @var \Ves\Megamenu\Helper\MobileDetect
     */
    protected $_mobileDetect;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $httpContext;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context            
     * @param \Ves\Megamenu\Model\Menu                         $menu               
     * @param \Magento\Customer\Model\Session                  $customerSession    
     * @param \Ves\Megamenu\Helper\MobileDetect                $mobileDetectHelper 
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array                                            $data
     * @param Json|null                                        $json                
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Megamenu\Model\Menu $menu,
        \Magento\Customer\Model\Session $customerSession,
        \Ves\Megamenu\Helper\MobileDetect $mobileDetectHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [],
        Json $json = null
        ) {
        parent::__construct($context);
        $this->setTemplate("widget/menu.phtml");
        $this->_menu            = $menu;
        $this->_mobileDetect    = $mobileDetectHelper;
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [\Ves\Megamenu\Model\Menu::CACHE_WIDGET_TAG]]);
    }
     public function getCustomerGroupId(){
        if(!isset($this->_customer_group_id)) {
            $this->_customer_group_id = (int)$this->_customerSession->getCustomerGroupId();
            $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
            if(!$isLoggedIn) {
               $this->_customer_group_id = 0;
            }
        }
        return $this->_customer_group_id;
        
    }
    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $menuId = $this->getData('id');
        $menuId = $menuId?$menuId:0;
        $code = $this->getConfig('alias');

        $conditions = $code.".".$menuId;
        $customerGroupId = (int)$this->getCustomerGroupId();
        $customerGroupId = $customerGroupId?("group".$customerGroupId):"group0";
        $conditions .= ".".$customerGroupId;
        if ($this->getMobileDetect()->isMobile()) {
            $conditions .= ".mobilemenu";
        }
        return [
        'VES_MEGAMENU_MENU_WIDGET',
        $this->_storeManager->getStore()->getId(),
        $this->_design->getDesignTheme()->getId(),
        $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
        'template' => $this->getTemplate(),
        $conditions,
        $this->json->serialize($this->getRequest()->getParams())
        ];
    }

    public function _toHtml() {
        $menu  = '';
        $menu = $this->getMenuProfile($this->getData('id'), $this->getData('alias'));
        if ($menu) {
            $customerGroups = $menu->getData('customer_group_ids');
            $customerGroupId = (int)$this->getCustomerGroupId();
            if(is_array($customerGroups) && !in_array($customerGroupId, $customerGroups)) {
                return;
            }
            $this->setData("menu", $menu);
        }
        return parent::_toHtml();
    }

    public function getMobileDetect() {
        return $this->_mobileDetect;
    }
    public function getMenuProfile($menuId = 0, $alias = ""){
        $menu = false;
        $store = $this->_storeManager->getStore();
        $customerGroupId = (int)$this->getCustomerGroupId();
        if($menuId){
            if($customerGroupId) {
                $menu = $this->_menu->setStore($store)
                                ->setLoggedCustomerGroupId($customerGroupId)
                                ->load((int)$menuId);
                if(!$menu->getId()) {
                    $menu = $this->_menu->setStore($store)
                                    ->load((int)$menuId);
                }
            } else {
                $menu = $this->_menu->setStore($store)->load((int)$menuId);
            }
            
            if ($menu->getId() != $menuId) {
                $menu = false;
            }
        } elseif($alias){
            if($customerGroupId) {
                $menu = $this->_menu->setStore($store)
                                ->setLoggedCustomerGroupId($customerGroupId)
                                ->load(addslashes($alias));
                if(!$menu->getId()) {
                    $menu = $this->_menu->setStore($store)
                                    ->load(addslashes($alias));
                }
            } else {
                $menu = $this->_menu->setStore($store)->load(addslashes($alias));
            }
            
            if ($menu->getAlias() != $alias) {
                $menu = false;
            }
        }
        if ($menu && !$menu->getStatus()) {
            $menu = false;
        }
        return $menu;
    }
    public function getMobileTemplateHtml($menuAlias)
    {
        $html = '';
        if($menuAlias){
            $html = $this->getLayout()->createBlock('Ves\Megamenu\Block\MobileMenu')->setData('alias', $menuAlias)->toHtml();
        }
        return $html;
    }
    public function getConfig($key, $default = NULL){
        if($this->hasData($key)){
            return $this->getData($key);
        }
        return $default;
    }
}