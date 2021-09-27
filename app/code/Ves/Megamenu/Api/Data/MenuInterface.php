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

interface MenuInterface
{

    const MENU_ID = 'menu_id';
    const ALIAS = 'alias';
    const MOBILE_TEMPLATE = 'mobile_template';
    const STRUCTURE = 'structure';
    const DISABLE_BELLOW = 'disable_bellow';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME = 'update_time';
    const DESKTOP_TEMPLATE = 'desktop_template';
    const DESIGN = 'design';
    const PARAMS = 'params';
    const DISABLE_IBLOCKS = 'disable_iblocks';
    const EVENT = 'event';
    const CLASSES = 'classes';
    const WIDTH = 'width';
    const SCROLLTOFIX = 'scrolltofix';
    const CURRENT_VERSION = 'current_version';
    const MOBILE_MENU_ALIAS = 'mobile_menu_alias';
    const MENU_ITEMS = 'menu_items';
    const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    const STORE_ID = 'store_id';
    const VERSION_ID = 'version_id';
    const MENU_TREE = 'menu_tree';
    const DESIGN_DECODE = 'design_decode';
    const REVERT_NEXT = 'revert_next';
    const REVERT_PREVIOUS = 'revert_previous';


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
     * Get alias
     * @return string|null
     */
    public function getAlias();

    /**
     * Set alias
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias);

    /**
     * Get mobile_template
     * @return string|null
     */
    public function getMobileTemplate();

    /**
     * Set mobile_template
     * @param string $mobile_template
     * @return $this
     */
    public function setMobileTemplate($mobile_template);

    /**
     * Get structure
     * @return string|null
     */
    public function getStructure();

    /**
     * Set structure
     * @param string $structure
     * @return $this
     */
    public function setStructure($structure);

    /**
     * Get disable_bellow
     * @return string|null
     */
    public function getDisableBellow();

    /**
     * Set disable_bellow
     * @param string $disable_bellow
     * @return $this
     */
    public function setDisableBellow($disable_bellow);

    /**
     * Get creation_time
     * @return string|null
     */
    public function getCretionTime();

    /**
     * Set creation_time
     * @param string $creation_time
     * @return $this
     */
    public function setCretionTime($creation_time);

    /**
     * Get update_time
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Set update_time
     * @param string $update_time
     * @return $this
     */
    public function setUpdateTime($update_time);

    /**
     * Get desktop_template
     * @return string|null
     */
    public function getDesktopTemplate();

    /**
     * Set desktop_template
     * @param string $desktop_template
     * @return $this
     */
    public function setDesktopTemplate($desktop_template);

     /**
     * Get design
     * @return string|null
     */
    public function getDesign();

    /**
     * Set design
     * @param string $design
     * @return $this
     */
    public function setDesign($design);

     /**
     * Get params
     * @return string|null
     */
    public function getParams();

    /**
     * Set params
     * @param string $params
     * @return $this
     */
    public function setParams($params);

    /**
     * Get disable_iblocks
     * @return string|null
     */
    public function getDisableIblocks();

    /**
     * Set disable_iblocks
     * @param string $disable_iblocks
     * @return $this
     */
    public function setDisableIblocks($disable_iblocks);

    /**
     * Get event
     * @return string|null
     */
    public function getEvent();

    /**
     * Set event
     * @param string $event
     * @return $this
     */
    public function setEvent($event);

    /**
     * Get classes
     * @return string|null
     */
    public function getClasses();

    /**
     * Set classes
     * @param string $classes
     * @return $this
     */
    public function setClasses($classes);

    /**
     * Get width
     * @return string|null
     */
    public function getWidth();

    /**
     * Set width
     * @param string $width
     * @return $this
     */
    public function setWidth($width);

    /**
     * Get scrolltofix
     * @return string|null
     */
    public function getScrolltofix();

    /**
     * Set scrolltofix
     * @param string $scrolltofix
     * @return $this
     */
    public function setScrolltofix($scrolltofix);

    /**
     * Get current_version
     * @return string|null
     */
    public function getCurrentVersion();

    /**
     * Set current_version
     * @param string $current_version
     * @return $this
     */
    public function setCurrentVersion($current_version);

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getMobileMenuAlias();

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setMobileMenuAlias($mobile_menu_alias);

    /**
     * Get menu items
     * @return string|null
     */
    public function getMenuItems();

    /**
     * Set menu_items
     * @param array $menu_items
     * @return $this
     */
    public function setMenuItems($menu_items);

    /**
     * Get store ids
     * @return mixed|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param mixed $store_id
     * @return $this
     */
    public function setStoreId($store_id);

    /**
     * Get customer_group_ids ids
     * @return mixed|null
     */
    public function getCustomerGroupIds();

    /**
     * Set customer_group_ids
     * @param mixed $customer_group_ids
     * @return $this
     */
    public function setCustomerGroupIds($customer_group_ids);

    /**
     * Get version_id
     * @return mixed|null
     */
    public function getVersionId();

    /**
     * Set version_id
     * @param mixed $version_id
     * @return $this
     */
    public function setVersionId($version_id);

    /**
     * Get menu_tree
     * @return mixed|null
     */
    public function getMenuTree();

    /**
     * Set menu_tree
     * @param mixed $menu_tree
     * @return $this
     */
    public function setMenuTree($menu_tree);

    /**
     * Get design_decode
     * @return mixed|null
     */
    public function getDesignDecode();

    /**
     * Set design_decode
     * @param mixed $design_decode
     * @return $this
     */
    public function setDesignDecode($design_decode);

    /**
     * Get revert_next
     * @return mixed|null
     */
    public function getRevertNext();

    /**
     * Set revert_next
     * @param mixed $revert_next
     * @return $this
     */
    public function setRevertNext($revert_next);

    /**
     * Get revert_previous
     * @return mixed|null
     */
    public function getRevertPrevious();

    /**
     * Set revert_previous
     * @param mixed $revert_previous
     * @return $this
     */
    public function setRevertPrevious($revert_previous);
}
