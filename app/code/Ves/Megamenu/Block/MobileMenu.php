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

class MobileMenu extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $_helper;

    /**
     * @var \\Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context         
     * @param \Ves\Megamenu\Helper\Data                        $helper          
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager   
     * @param \Magento\Customer\Model\Session                  $customerSession 
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array                                            $data            
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Megamenu\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper          = $helper;
        $this->_objectManager   = $objectManager;
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
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
    public function _toHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate("Ves_Megamenu::mobile_menu.phtml");
        }

        $store = $this->_storeManager->getStore();
        $html  = $menu = '';

        $menu = $this->getData('menu');
        $alias = $this->getData('alias');
        $menu_id = $this->getData('id');

        if(!$menu) {
            $menu = $this->getMenuProfile($menu_id, $alias);
        }
        if ($menu) {
            $customerGroups = $menu->getData('customer_group_ids');
            $customerGroupId = (int)$this->getCustomerGroupId();
            if ($customerGroupId) {
                if(!in_array($customerGroupId, $customerGroups)) return;
            }

            $this->setData("menu", $menu);
        }

        return parent::_toHtml();
    }
    public function getMenuProfile($menuId = 0, $alias = ""){
        $menu = false;
        $store = $this->_storeManager->getStore();
        if($menuId){
            $menu = $this->_menu->setStore($store)->load((int)$menuId);
            if ($menu->getId() != $menuId) {
                $menu = false;
            }
        } elseif($alias){
            $menu = $this->_menu->setStore($store)->load(addslashes($alias));
            if ($menu->getAlias() != $alias) {
                $menu = false;
            }
        }
        if ($menu && !$menu->getStatus()) {
            $menu = false;
        }
        return $menu;
    }
    public function getConfig($key, $default = NULL){
        if($this->hasData($key)){
            return $this->getData($key);
        }
        return $default;
    }
}
