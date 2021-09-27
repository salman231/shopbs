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

namespace Ves\Megamenu\Api\Data;

interface ItemInterface
{

    const ID = 'id';
    const ITEM_ID = 'item_id';
    const MENU_ID = 'menu_id';
    const NAME = 'name';
    const SHOW_NAME = 'show_name';
    const CLASSES = 'classes';
    const CHILD_COL = 'child_col';
    const SUB_WIDTH = 'sub_width';
    const ALIGN = 'align';
    const ICON_POSITION = 'icon_position';
    const ICON_CLASSES = 'icon_classes';
    const IS_GROUP = 'is_group';
    const STATUS = 'status';
    const DISABLE_BELLOW = 'disable_bellow';
    const SHOW_ICON = 'show_icon';
    const ICON = 'icon';
    const SHOW_HEADER = 'show_header';
    const HEADER_HTML = 'header_html';
    const SHOW_LEFT_SIDEBAR = 'show_left_sidebar';
    const LEFT_SIDEBAR_WIDTH = 'left_sidebar_width';
    const LEFT_SIDEBAR_HTML = 'left_sidebar_html';
    const SHOW_CONTENT = 'show_content';
    const CONTENT_WIDTH = 'content_width';
    const CONTENT_TYPE = 'content_type';
    const LINK_TYPE = 'link_type';
    const LINK = 'link';
    const CATEGORY = 'category';
    const TARGET = 'target';
    const CONTENT_HTML = 'content_html';
    const SHOW_RIGHT_SIDEBAR = 'show_right_sidebar';
    const RIGHT_SIDEBAR_WIDTH = 'right_sidebar_width';
    const RIGHT_SIDEBAR_HTML = 'right_sidebar_html';
    const SHOW_FOOTER = 'show_footer';
    const FOOTER_HTML = 'footer_html';
    const COLOR = 'color';
    const HOVER_COLOR = 'hover_color';
    const BG_COLOR = 'bg_color';
    const BG_HOVER_COLOR = 'bg_hover_color';
    const INLINE_CSS = 'inline_css';
    const TAB_POSITION = 'tab_position';
    const BEFORE_HTML = 'before_html';
    const AFTER_HTML = 'after_html';
    const CARET = 'caret';
    const HOVER_CARET = 'hover_caret';
    const SUB_HEIGHT = 'sub_height';
    const HOVER_ICON = 'hover_icon';
    const DROPDOWN_BGCOLOR = 'dropdown_bgcolor';
    const DROPDOWN_BGIMAGE = 'dropdown_bgimage';
    const DROPDOWN_BGIMAGEREPEAT = 'dropdown_bgimagerepeat';
    const DROPDOWN_BGPOSITIONX = 'dropdown_bgpositionx';
    const DROPDOWN_BGPOSITIONY = 'dropdown_bgpositiony';
    const DROPDOWN_INLINECSS = 'dropdown_inlinecss';
    const PARENTCAT = 'parentcat';
    const ANIMATION_IN = 'animation_in';
    const ANIMATION_TIME = 'animation_time';
    const CHILD_COL_TYPE = 'child_col_type';
    const SUBMENU_SORTTYPE = 'submenu_sorttype';
    const ISGROUP_LEVEL = 'isgroup_level';


    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return $this
     */
    public function setId($Id);

    /**
     * Get item_id
     * @return string|null
     */
    public function getItemId();

    /**
     * Set item_id
     * @param string $item_id
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Get menu_id
     * @return string|null
     */
    public function getMenuId();

    /**
     * Set menu_id
     * @param string $menu_id
     * @return $this
     */
    public function setMenuId($menuId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get show_name
     * @return string|null
     */
    public function getShowName();

    /**
     * Set show_name
     * @param string $show_name
     * @return $this
     */
    public function setShowName($show_name);

    /**
     * Get structure
     * @return string|null
     */
    public function getClasses();

    /**
     * Set structure
     * @param string $structure
     * @return $this
     */
    public function setClasses($classes);

    /**
     * Get disable_bellow
     * @return string|null
     */
    public function getChildCol();

    /**
     * Set disable_bellow
     * @param string $disable_bellow
     * @return $this
     */
    public function setChildCol($child_col);

    /**
     * Get creation_time
     * @return string|null
     */
    public function getSubWidth();

    /**
     * Set creation_time
     * @param string $creation_time
     * @return $this
     */
    public function setSubWidth($sub_width);

    /**
     * Get update_time
     * @return string|null
     */
    public function getAlign();

    /**
     * Set update_time
     * @param string $update_time
     * @return $this
     */
    public function setAlign($align);

    /**
     * Get desktop_template
     * @return string|null
     */
    public function getIconPosition();

    /**
     * Set desktop_template
     * @param string $desktop_template
     * @return $this
     */
    public function setIconPosition($icon_position);

     /**
     * Get design
     * @return string|null
     */
    public function getIconClasses();

    /**
     * Set design
     * @param string $design
     * @return $this
     */
    public function setIconClasses($icon_classes);

     /**
     * Get params
     * @return string|null
     */
    public function getIsGroup();

    /**
     * Set params
     * @param string $params
     * @return $this
     */
    public function setIsGroup($is_group);

    /**
     * Get disable_iblocks
     * @return string|null
     */
    public function getStatus();

    /**
     * Set disable_iblocks
     * @param string $disable_iblocks
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get event
     * @return string|null
     */
    public function getDisableBellow();

    /**
     * Set event
     * @param string $event
     * @return $this
     */
    public function setDisableBellow($disable_bellow);

    /**
     * Get classes
     * @return string|null
     */
    public function getShowIcon();

    /**
     * Set classes
     * @param string $classes
     * @return $this
     */
    public function setShowIcon($show_icon);

    /**
     * Get width
     * @return string|null
     */
    public function getIcon();

    /**
     * Set width
     * @param string $width
     * @return $this
     */
    public function setIcon($icon);

    /**
     * Get scrolltofix
     * @return string|null
     */
    public function getShowHeader();

    /**
     * Set scrolltofix
     * @param string $scrolltofix
     * @return $this
     */
    public function setShowHeader($show_header);

    /**
     * Get current_version
     * @return string|null
     */
    public function getHeaderHtml();

    /**
     * Set current_version
     * @param string $current_version
     * @return $this
     */
    public function setHeaderHtml($header_html);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getShowLeftSidebar();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setShowLeftSidebar($show_left_sidebar);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getLeftSidebarWidth();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setLeftSidebarWidth($left_sidebar_width);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getLeftSidebarHtml();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setLeftSidebarHtml($left_sidebar_html);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getShowContent();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setShowContent($show_content);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getContentWidth();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setContentWidth($content_width);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getContentType();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setContentType($content_type);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getLinkType();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setLinkType($link_type);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getLink();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setLink($link);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getCategory();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setCategory($category);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getTarget();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setTarget($target);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getContentHtml();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setContentHtml($content_html);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getShowRightSidebar();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setShowRightSidebar($show_right_sidebar);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getRightSidebarWidth();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setRightSidebarWidth($right_sidebar_width);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getRightSidebarHtml();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setRightSidebarHtml($right_sidebar_html);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getShowFooter();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setShowFooter($show_footer);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getFooterHtml();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setFooterHtml($footer_html);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getColor();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setColor($color);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getHoverColor();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setHoverColor($hover_color);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getBgColor();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setBgColor($bg_color);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getBgHoverColor();

    /**
     * Set bg_hover_color
     * @param string $bg_hover_color
     * @return $this
     */
    public function setBgHoverColor($bg_hover_color);

    /**
     * Get inline_css
     * @return string|null
     */
    public function getInlinceCss();

    /**
     * Set inline_css
     * @param string $inline_css
     * @return $this
     */
    public function setInlinceCss($inline_css);

    /**
     * Get tab_position
     * @return string|null
     */
    public function getTabPosition();

    /**
     * Set tab_position
     * @param string $tab_position
     * @return $this
     */
    public function setTabPosition($tab_position);

    /**
     * Get before_html
     * @return string|null
     */
    public function getBeforeHtml();

    /**
     * Set before_html
     * @param string $before_html
     * @return $this
     */
    public function setBeforeHtml($before_html);

    /**
     * Get after_html
     * @return string|null
     */
    public function getAfterHtml();

    /**
     * Set after_html
     * @param string $after_html
     * @return $this
     */
    public function setAfterHtml($after_html);

    /**
     * Get caret
     * @return string|null
     */
    public function getCaret();

    /**
     * Set caret
     * @param string $caret
     * @return $this
     */
    public function setCaret($caret);

    /**
     * Get hover_caret
     * @return string|null
     */
    public function getHoverCaret();

    /**
     * Set hover_caret
     * @param string $hover_caret
     * @return $this
     */
    public function setHoverCaret($hover_caret);

    /**
     * Get sub_height
     * @return string|null
     */
    public function getSubHeight();

    /**
     * Set sub_height
     * @param string $sub_height
     * @return $this
     */
    public function setSubHeight($sub_height);

    /**
     * Get hover_icon
     * @return string|null
     */
    public function getHoverIcon();

    /**
     * Set hover_icon
     * @param string $hover_icon
     * @return $this
     */
    public function setHoverIcon($hover_icon);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getDropdownBgcolor();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setDropdownBgcolor($dropdown_bgcolor);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getDropdownBgimage();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setDropdownBgimage($dropdown_bgimage);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getDropdownBgimagerepeat();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setDropdownBgimagerepeat($dropdown_bgimagerepeat);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getDropdownBgpositionx();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setDropdownBgpositionx($dropdown_bgpositionx);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getDropdownBgpositiony();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setDropdownBgpositiony($dropdown_bgpositiony);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getDropdownInlinecss();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setDropdownInlinecss($dropdown_inlinecss);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getParentcat();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setParentcat($parentcat);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getAnimationIn();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setAnimationIn($animation_in);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getAnimationTime();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setAnimationTime($animation_time);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getChildColType();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setChildColType($child_col_type);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getSubmenuSorttype();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setSubmenuSorttype($submenu_sorttype);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getIsgroupLevel();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setIsgroupLevel($isgroup_level);

}
