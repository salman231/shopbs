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

class Categories extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $_helper;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $httpContext;

    protected $_image_helper;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Megamenu\Model\Menu $menu,
        \Magento\Customer\Model\Session $customerSession,
        \Ves\Megamenu\Helper\Data $helperData,
        \Ves\Megamenu\Helper\ImageAsset $imageAssetHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = [],
        Json $json = null
        ) {
        parent::__construct($context);
        $this->setTemplate("widget/category_list.phtml");
        $this->_menu            = $menu;
        $this->_helper    = $helperData;
        $this->_image_helper = $imageAssetHelper;
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
        $layout_template = $this->getConfig("layout_template");
        $custom_template = $this->getConfig("custom_template");

        if($layout_template == "grid"){
            $layout_template = "widget/category_grid.phtml";
            die("teate");
        }else {
            $layout_template = "widget/category_list.phtml";
        }
        if($custom_template){
            $this->setTemplate($custom_template);
        } elseif($layout_template){
            $this->setTemplate($layout_template);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [\Ves\Megamenu\Model\Menu::CACHE_WIDGET_CATEGORIES_TAG]]);
    }
     public function getCustomerGroupId(){
        if(!isset($this->_customer_group_id)) {
            $this->_customer_group_id = (int)$this->_customerSession->getCustomerGroupId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $context = $objectManager->get('Magento\Framework\App\Http\Context');
            $isLoggedIn = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
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
        $source_type = $this->getConfig("source_type");
        $parent_category = $this->getConfig("parent_category");
        $categories = $this->getConfig("categories");
        $categories = is_array($categories)?implode(".",$categories):$categories;
        $limit = $this->getConfig("limit");
        $layout_template = $this->getConfig("layout_template");
        $custom_template = $this->getConfig("custom_template");
        $conditions = $source_type.".".$parent_category.".".$categories.".".$limit.".".$layout_template;
        
        return [
        'VES_MEGAMENU_CATEGORIES',
        $this->_storeManager->getStore()->getId(),
        $this->_design->getDesignTheme()->getId(),
        $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
        'template' => $this->getTemplate(),
        $conditions,
        $this->json->serialize($this->getRequest()->getParams())
        ];
    }

    public function _toHtml() {
        $source_type = $this->getConfig("source_type");
        $sort_type = $this->getConfig("submenu_sorttype", 'normal');
        $max_level = $this->getConfig("max_level", 100);
        $limit = $this->getConfig("limit",0);
        $show_image = $this->getConfig("show_image", false);
        $show_name = $this->getConfig("show_name", true);
        $image_width = $this->getConfig("image_width", 100);
        $image_height = $this->getConfig("image_height", 0 );
        $title = $this->getConfig("title", '');
        $custom_link = $this->getConfig("custom_link", '');
        $show_more_items = $this->getConfig("show_more_items", false);
        $menu_level = $this->getConfig("menu_level", 0);
        $number_column = $this->getConfig("number_column",1);

        $children = [];
        if($source_type == "parent") {
            $parentcat = $this->getConfig("parent_category",0);
            if($parentcat) {
                $isgroup_level = $this->getConfig("isgroup_level", 0);
                $level = 1;
                $list = [];
                $catChildren = $this->_helper->resetData()->getTreeCategories($parentcat,$level,$list,$max_level,1, $sort_type, $show_image, $limit);
                $children = $this->_helper->sortMenuItems($catChildren, $sort_type);
                $this->setData("menu_items", $children);
            }
        } else{
            $catids = $this->getConfig("categories","");
            if(!is_array($catids)){
                $catids = explode(",",$catids);
                $helper = $this->_helper->resetData()->setMenuCategories($catids);
                $categories = $helper->getRootCategory(true);
                if($categories){
                    //convert to menu items
                    $catChildren = $helper->convertCategoriesToMenuItems($categories, $show_image);
                    $children = $helper->sortMenuItems($catChildren, $sort_type);
                    $children = $helper->initMenuItems($children, $catids);
                    $this->setData("menu_items", $children);
                }
            }
        }
        $this->setData("show_image", $show_image);
        $this->setData("show_name", $show_name);
        $this->setData("show_more_items", $show_more_items);
        $this->setData("title", $title);
        $this->setData("custom_link", $custom_link);
        $this->setData("image_width", $image_width);
        $this->setData("image_width", $image_height);
        $this->setData("level", (int)$menu_level);
        $this->getData("number_column", (int)$number_column);

        $layout_template = $this->getConfig("layout_template");
        $custom_template = $this->getConfig("custom_template");

        if($layout_template == "grid"){
            $layout_template = "widget/category_grid.phtml";
        }else {
            $layout_template = "widget/category_list.phtml";
        }
        if($custom_template){
            $this->setTemplate($custom_template);
        } elseif($layout_template){
            $this->setTemplate($layout_template);
        }

        return parent::_toHtml();
    }

    public function getConfig($key, $default = NULL){
        if($this->hasData($key)){
            return $this->getData($key);
        }
        return $default;
    }

    public function getCategoryImage($image_file = "") {
        $image_file_path = "";
        if($image_file) {
            $image_width = $this->getConfig("image_width", 100);
            $image_height = $this->getConfig("image_height", 0);
            $image_file = 'catalog/category/'.$image_file;
            $image_file_path = $this->_image_helper->resizeImage($image_file, (int)$image_width, (int)$image_height);
        }
        return $image_file_path;
    }
}