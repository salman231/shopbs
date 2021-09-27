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

namespace Ves\Megamenu\Model;

use Ves\Megamenu\Api\Data\ItemInterface;

class Item extends \Magento\Framework\Model\AbstractModel implements ItemInterface
{
	const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $menuHelper;

    /**
     * @param \Magento\Framework\Model\Context                          $context                  
     * @param \Magento\Framework\Registry                               $registry       
     * @param \Ves\Megamenu\Model\ResourceModel\Item|null                      $resource                    
     * @param \Ves\Megamenu\Model\ResourceModel\Item\Collection|null           $resourceCollection   
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager    
     * @param \Magento\Framework\UrlInterface                           $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig                              
     * @param \Ves\Megamenu\Helper\Data                                    $menuHelper              
     * @param array                                                     $data                     
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Megamenu\Model\ResourceModel\Item $resource = null,
        \Ves\Megamenu\Model\ResourceModel\Item\Collection $resourceCollection = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ves\Megamenu\Helper\Data                                    $menuHelper,
        array $data = []
        ) {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_resource = $resource;
        $this->scopeConfig = $scopeConfig;
        $this->menuHelper = $menuHelper;
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ves\Megamenu\Model\ResourceModel\Item');
    }
    /**
     * render Html and Short code for menu item
     * @param string $storeId
     * @return $this
     */
    public function renderHtmlShortcode($storeId = 'all', $isBackend = false){
        if(!$isBackend) {
            $header_html = $this->getHeaderHtml();
            $link = $this->getLink();
            $footer_html = $this->getFooterHtml();
            $left_sidebar_html = $this->getLeftSidebarHtml();
            $right_sidebar_html = $this->getRightSidebarHtml();
            $before_html = $this->getBeforeHtml();
		    $after_html = $this->getAfterHtml();

            if($link){
                $link = $this->menuHelper->filter($link, $storeId);
                $link = trim($link);
                if ($this->menuHelper->endsWith($link, '/')) {
                    $link = substr_replace($link, "", -1);	
                }
                $this->setLink($link);
            }
            if($header_html){
                $header_html = $this->menuHelper->decodeWidgets($header_html, $storeId);
                $header_html = $this->menuHelper->filter($header_html, $storeId);
                $this->setHeaderHtml($header_html);
            }
            if($footer_html){
                $footer_html = $this->menuHelper->decodeWidgets($footer_html, $storeId);
                $footer_html = $this->menuHelper->filter($footer_html, $storeId);
                $this->setFooterHtml($footer_html);
            }
            if($left_sidebar_html){
                $left_sidebar_html = $this->menuHelper->decodeWidgets($left_sidebar_html, $storeId);
                $left_sidebar_html = $this->menuHelper->filter($left_sidebar_html, $storeId);
                $this->setLeftSideBarHtml($left_sidebar_html);
            }
            if($right_sidebar_html){
                $right_sidebar_html = $this->menuHelper->decodeWidgets($right_sidebar_html, $storeId);
                $right_sidebar_html = $this->menuHelper->filter($right_sidebar_html, $storeId);
                $this->setRightSidebarHtml($right_sidebar_html);
            }
            if($before_html){
                $before_html = $this->menuHelper->decodeWidgets($before_html, $storeId);
                $before_html = $this->menuHelper->filter($before_html, $storeId);
                $this->setBeforeHtml($before_html);
            }
            if($after_html){
                $after_html = $this->menuHelper->decodeWidgets($after_html, $storeId);
                $after_html = $this->menuHelper->filter($after_html, $storeId);
                $this->setAfterHtml($after_html);
            }
        }
        return $this;
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get id
     * @return string|null
     */
    public function getId(){
        return $this->getData(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return $this
     */
    public function setId($id){
        return $this->setData(self::ID, (int)$id);
    }

    /**
     * Get item_id
     * @return string|null
     */
    public function getItemId(){
        return $this->getData(self::ITEM_ID);
    }

    /**
     * Set item_id
     * @param string $item_id
     * @return $this
     */
    public function setItemId($itemId){
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * Get menu_id
     * @return string|null
     */
    public function getMenuId(){
        return $this->getData(self::MENU_ID);
    }

    /**
     * Set menu_id
     * @param string $menu_id
     * @return $this
     */
    public function setMenuId($menuId){
        return $this->setData(self::MENU_ID, $menuId);
    }

    /**
     * Get name
     * @return string|null
     */
    public function getName(){
        return $this->getData(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return $this
     */
    public function setName($name){
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get show_name
     * @return string|null
     */
    public function getShowName(){
        return $this->getData(self::SHOW_NAME);
    }

    /**
     * Set show_name
     * @param string $show_name
     * @return $this
     */
    public function setShowName($show_name){
        return $this->setData(self::SHOW_NAME, $show_name);
    }

    /**
     * Get structure
     * @return string|null
     */
    public function getClasses(){
        return $this->getData(self::CLASSES);
    }

    /**
     * Set structure
     * @param string $structure
     * @return $this
     */
    public function setClasses($classes){
        return $this->setData(self::CLASSES, $classes);
    }

    /**
     * Get disable_bellow
     * @return string|null
     */
    public function getChildCol(){
        return $this->getData(self::CHILD_COL);
    }

    /**
     * Set disable_bellow
     * @param string $disable_bellow
     * @return $this
     */
    public function setChildCol($child_col){
        return $this->setData(self::CHILD_COL, $child_col);
    }

    /**
     * Get creation_time
     * @return string|null
     */
    public function getSubWidth(){
        return $this->getData(self::SUB_WIDTH);
    }

    /**
     * Set creation_time
     * @param string $creation_time
     * @return $this
     */
    public function setSubWidth($sub_width){
        return $this->setData(self::SUB_WIDTH, $sub_width);
    }

    /**
     * Get update_time
     * @return string|null
     */
    public function getAlign(){
        return $this->getData(self::ALIGN);
    }

    /**
     * Set update_time
     * @param string $update_time
     * @return $this
     */
    public function setAlign($align){
        return $this->setData(self::ALIGN, $align);
    }

    /**
     * Get desktop_template
     * @return string|null
     */
    public function getIconPosition(){
        return $this->getData(self::ICON_POSITION);
    }

    /**
     * Set desktop_template
     * @param string $desktop_template
     * @return $this
     */
    public function setIconPosition($icon_position){
        return $this->setData(self::ICON_POSITION, $icon_position);
    }

     /**
     * Get design
     * @return string|null
     */
    public function getIconClasses(){
        return $this->getData(self::ICON_CLASSES);
    }

    /**
     * Set design
     * @param string $design
     * @return $this
     */
    public function setIconClasses($icon_classes){
        return $this->setData(self::ICON_CLASSES, $icon_classes);
    }

     /**
     * Get params
     * @return string|null
     */
    public function getIsGroup(){
        return $this->getData(self::IS_GROUP);
    }

    /**
     * Set params
     * @param string $params
     * @return $this
     */
    public function setIsGroup($is_group){
        return $this->setData(self::IS_GROUP, $is_group);
    }

    /**
     * Get disable_iblocks
     * @return string|null
     */
    public function getStatus(){
        return $this->getData(self::STATUS);
    }

    /**
     * Set disable_iblocks
     * @param string $disable_iblocks
     * @return $this
     */
    public function setStatus($status){
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get event
     * @return string|null
     */
    public function getDisableBellow(){
        return $this->getData(self::DISABLE_BELLOW);
    }

    /**
     * Set event
     * @param string $event
     * @return $this
     */
    public function setDisableBellow($disable_bellow){
        return $this->setData(self::DISABLE_BELLOW, $disable_bellow);
    }

    /**
     * Get classes
     * @return string|null
     */
    public function getShowIcon(){
        return $this->getData(self::SHOW_ICON);
    }

    /**
     * Set classes
     * @param string $classes
     * @return $this
     */
    public function setShowIcon($show_icon){
        return $this->setData(self::SHOW_ICON, $show_icon);
    }

    /**
     * Get width
     * @return string|null
     */
    public function getIcon(){
        return $this->getData(self::ICON);
    }

    /**
     * Set width
     * @param string $width
     * @return $this
     */
    public function setIcon($icon){
        return $this->setData(self::ICON, $icon);
    }

    /**
     * Get scrolltofix
     * @return string|null
     */
    public function getShowHeader(){
        return $this->getData(self::SHOW_HEADER);
    }

    /**
     * Set scrolltofix
     * @param string $scrolltofix
     * @return $this
     */
    public function setShowHeader($show_header){
        return $this->setData(self::SHOW_HEADER, $show_header);
    }

    /**
     * Get current_version
     * @return string|null
     */
    public function getHeaderHtml(){
        return $this->getData(self::HEADER_HTML);
    }

    /**
     * Set current_version
     * @param string $current_version
     * @return $this
     */
    public function setHeaderHtml($header_html){
        return $this->setData(self::HEADER_HTML, $header_html);
    }

    /**
     * {@inheritdoc}
     */
    public function getShowLeftSidebar(){
        return $this->getData(self::SHOW_LEFT_SIDEBAR);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowLeftSidebar($show_left_sidebar){
        return $this->setData(self::SHOW_LEFT_SIDEBAR, $show_left_sidebar);
    }

    /**
     * {@inheritdoc}
     */
    public function getLeftSidebarWidth(){
        return $this->getData(self::LEFT_SIDEBAR_WIDTH);
    }

    /**
     * {@inheritdoc}
     */
    public function setLeftSidebarWidth($left_sidebar_width){
        return $this->setData(self::LEFT_SIDEBAR_WIDTH, $left_sidebar_width);
    }

    /**
     * {@inheritdoc}
     */
    public function getLeftSidebarHtml(){
        return $this->getData(self::LEFT_SIDEBAR_HTML);
    }

    /**
     * {@inheritdoc}
     */
    public function setLeftSidebarHtml($left_sidebar_html){
        return $this->setData(self::LEFT_SIDEBAR_HTML, $left_sidebar_html);
    }

    /**
     * {@inheritdoc}
     */
    public function getShowContent(){
        return $this->getData(self::SHOW_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowContent($show_content){
        return $this->setData(self::SHOW_CONTENT, $show_content);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentWidth(){
        return $this->getData(self::CONTENT_WIDTH);
    }

    /**
     * {@inheritdoc}
     */
    public function setContentWidth($content_width){
        return $this->setData(self::CONTENT_WIDTH, $content_width);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(){
        return $this->getData(self::CONTENT_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setContentType($content_type){
        return $this->setData(self::CONTENT_TYPE, $content_type);
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkType(){
        return $this->getData(self::LINK_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setLinkType($link_type){
        return $this->setData(self::LINK_TYPE, $link_type);
    }

    /**
     * {@inheritdoc}
     */
    public function getLink(){
        return $this->getData(self::LINK);
    }

    /**
     * {@inheritdoc}
     */
    public function setLink($link){
        return $this->setData(self::LINK, $link);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory(){
        return $this->getData(self::CATEGORY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategory($category){
        return $this->setData(self::CATEGORY, $category);
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget(){
        return $this->getData(self::TARGET);
    }

    /**
     * {@inheritdoc}
     */
    public function setTarget($target){
        return $this->setData(self::TARGET, $target);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentHtml(){
        return $this->getData(self::CONTENT_HTML);
    }

    /**
     * {@inheritdoc}
     */
    public function setContentHtml($content_html){
        return $this->setData(self::CONTENT_HTML, $content_html);
    }

    /**
     * {@inheritdoc}
     */
    public function getShowRightSidebar(){
        return $this->getData(self::SHOW_RIGHT_SIDEBAR);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowRightSidebar($show_right_sidebar){
        return $this->setData(self::SHOW_RIGHT_SIDEBAR, $show_right_sidebar);
    }

    /**
     * {@inheritdoc}
     */
    public function getRightSidebarWidth(){
        return $this->getData(self::RIGHT_SIDEBAR_WIDTH);
    }

    /**
     * {@inheritdoc}
     */
    public function setRightSidebarWidth($right_sidebar_width){
        return $this->setData(self::RIGHT_SIDEBAR_WIDTH, $right_sidebar_width);
    }

    /**
     * {@inheritdoc}
     */
    public function getRightSidebarHtml(){
        return $this->getData(self::RIGHT_SIDEBAR_HTML);
    }

    /**
     * {@inheritdoc}
     */
    public function setRightSidebarHtml($right_sidebar_html){
        return $this->setData(self::RIGHT_SIDEBAR_HTML, $right_sidebar_html);
    }

    /**
     * {@inheritdoc}
     */
    public function getShowFooter(){
        return $this->getData(self::SHOW_FOOTER);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowFooter($show_footer){
        return $this->setData(self::SHOW_FOOTER, $show_footer);
    }

    /**
     * {@inheritdoc}
     */
    public function getFooterHtml(){
        return $this->getData(self::FOOTER_HTML);
    }

    /**
     * {@inheritdoc}
     */
    public function setFooterHtml($footer_html){
        return $this->setData(self::FOOTER_HTML, $footer_html);
    }

    /**
     * {@inheritdoc}
     */
    public function getColor(){
        return $this->getData(self::COLOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setColor($color){
        return $this->setData(self::COLOR, $color);
    }

    /**
     * {@inheritdoc}
     */
    public function getHoverColor(){
        return $this->getData(self::HOVER_COLOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setHoverColor($hover_color){
        return $this->setData(self::HOVER_COLOR, $hover_color);
    }

    /**
     * {@inheritdoc}
     */
    public function getBgColor(){
        return $this->getData(self::BG_COLOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setBgColor($bg_color){
        return $this->setData(self::BG_COLOR, $bg_color);
    }

    /**
     * {@inheritdoc}
     */
    public function getBgHoverColor(){
        return $this->getData(self::BG_HOVER_COLOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setBgHoverColor($bg_hover_color){
        return $this->setData(self::BG_HOVER_COLOR, $bg_hover_color);
    }

    /**
     * {@inheritdoc}
     */
    public function getInlinceCss(){
        return $this->getData(self::INLINE_CSS);
    }

    /**
     * {@inheritdoc}
     */
    public function setInlinceCss($inline_css){
        return $this->setData(self::INLINE_CSS, $inline_css);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabPosition(){
        return $this->getData(self::TAB_POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setTabPosition($tab_position){
        return $this->setData(self::TAB_POSITION, $tab_position);
    }

    /**
     * {@inheritdoc}
     */
    public function getBeforeHtml(){
        return $this->getData(self::BEFORE_HTML);
    }

    /**
     * {@inheritdoc}
     */
    public function setBeforeHtml($before_html){
        return $this->setData(self::BEFORE_HTML, $before_html);
    }

    /**
     * {@inheritdoc}
     */
    public function getCaret(){
        return $this->getData(self::CARET);
    }

    /**
     * {@inheritdoc}
     */
    public function setCaret($caret){
        return $this->setData(self::CARET, $caret);
    }

    /**
     * {@inheritdoc}
     */
    public function getHoverCaret(){
        return $this->getData(self::HOVER_CARET);
    }

    /**
     * {@inheritdoc}
     */
    public function setHoverCaret($hover_caret){
        return $this->setData(self::HOVER_CARET, $hover_caret);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubHeight(){
        return $this->getData(self::SUB_HEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubHeight($sub_height){
        return $this->setData(self::SUB_HEIGHT, $sub_height);
    }

    /**
     * {@inheritdoc}
     */
    public function getHoverIcon(){
        return $this->getData(self::HOVER_ICON);
    }

    /**
     * {@inheritdoc}
     */
    public function setHoverIcon($hover_icon){
        return $this->setData(self::HOVER_ICON, $hover_icon);
    }

    /**
     * {@inheritdoc}
     */
    public function getDropdownBgcolor(){
        return $this->getData(self::DROPDOWN_BGCOLOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setDropdownBgcolor($dropdown_bgcolor){
        return $this->setData(self::DROPDOWN_BGCOLOR, $dropdown_bgcolor);
    }

    /**
     * {@inheritdoc}
     */
    public function getDropdownBgimage(){
        return $this->getData(self::DROPDOWN_BGIMAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDropdownBgimage($dropdown_bgimage){
        return $this->setData(self::DROPDOWN_BGIMAGE, $dropdown_bgimage);
    }

    /**
     * {@inheritdoc}
     */
    public function getDropdownBgimagerepeat(){
        return $this->getData(self::DROPDOWN_BGIMAGEREPEAT);
    }

    /**
     * {@inheritdoc}
     */
    public function setDropdownBgimagerepeat($dropdown_bgimagerepeat){
        return $this->setData(self::DROPDOWN_BGIMAGEREPEAT, $dropdown_bgimagerepeat);
    }

    /**
     * {@inheritdoc}
     */
    public function getDropdownBgpositionx(){
        return $this->getData(self::DROPDOWN_BGPOSITIONX);
    }

    /**
     * {@inheritdoc}
     */
    public function setDropdownBgpositionx($dropdown_bgpositionx){
        return $this->setData(self::DROPDOWN_BGPOSITIONX, $dropdown_bgpositionx);
    }

    /**
     * {@inheritdoc}
     */
    public function getDropdownBgpositiony(){
        return $this->getData(self::DROPDOWN_BGPOSITIONY);
    }

    /**
     * {@inheritdoc}
     */
    public function setDropdownBgpositiony($dropdown_bgpositiony){
        return $this->setData(self::DROPDOWN_BGPOSITIONY, $dropdown_bgpositiony);
    }

    /**
     * {@inheritdoc}
     */
    public function getDropdownInlinecss(){
        return $this->getData(self::DROPDOWN_INLINECSS);
    }

    /**
     * {@inheritdoc}
     */
    public function setDropdownInlinecss($dropdown_inlinecss){
        return $this->setData(self::DROPDOWN_INLINECSS, $dropdown_inlinecss);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentcat(){
        return $this->getData(self::PARENTCAT);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentcat($parentcat){
        return $this->setData(self::PARENTCAT, $parentcat);
    }

    /**
     * {@inheritdoc}
     */
    public function getAnimationIn(){
        return $this->getData(self::ANIMATION_IN);
    }

    /**
     * {@inheritdoc}
     */
    public function setAnimationIn($animation_in){
        return $this->setData(self::ANIMATION_IN, $animation_in);
    }

    /**
     * {@inheritdoc}
     */
    public function getAnimationTime(){
        return $this->getData(self::ANIMATION_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setAnimationTime($animation_time){
        return $this->setData(self::ANIMATION_TIME, $animation_time);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildColType(){
        return $this->getData(self::CHILD_COL_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setChildColType($child_col_type){
        return $this->setData(self::CHILD_COL_TYPE, $child_col_type);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubmenuSorttype(){
        return $this->getData(self::SUBMENU_SORTTYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubmenuSorttype($submenu_sorttype){
        return $this->setData(self::SUBMENU_SORTTYPE, $submenu_sorttype);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsgroupLevel(){
        return $this->getData(self::ISGROUP_LEVEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsgroupLevel($isgroup_level){
        return $this->setData(self::ISGROUP_LEVEL, $isgroup_level);
    }

    /**
     * {@inheritdoc}
     */
    public function getAfterHtml(){
        return $this->getData(self::AFTER_HTML);
    }

    /**
     * {@inheritdoc}
     */
    public function setAfterHtml($after_html){
        return $this->setData(self::AFTER_HTML, $after_html);
    }
}