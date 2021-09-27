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

use Ves\Megamenu\Api\Data\MenuInterface;

class Menu extends \Magento\Framework\Model\AbstractModel implements MenuInterface
{
	const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Menu cache tag
     */
    const CACHE_TAG = 'megamenu_menu';
    const CACHE_HTML_TAG = 'megamenu_menu_html';

    /**
     * Menu cache tag
     */
    const CACHE_WIDGET_TAG = 'megamenu_menu_widget';

    const MENU_TEMPLATE_TYPE_CUSTOM = 2;
    const MENU_TEMPLATE_TYPE_ACCORDION = 1;
    const MENU_TEMPLATE_TYPE_OFF_CANVAS = 0;
    const MENU_TEMPLATE_TYPE_DRILL = 3;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_blogHelper;

    protected $_resource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_store;

    protected $_logged_customer_group_id;

    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $menuHelper;

    /**
     * @param \Magento\Framework\Model\Context                          $context                  
     * @param \Magento\Framework\Registry                               $registry       
     * @param \Ves\Megamenu\Model\ResourceModel\Menu|null                      $resource                    
     * @param \Ves\Megamenu\Model\ResourceModel\Menu\Collection|null           $resourceCollection   
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager    
     * @param \Magento\Framework\UrlInterface                           $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig                              
     * @param \Ves\Megamenu\Helper\Data                                    $menuHelper              
     * @param array                                                     $data                     
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Megamenu\Model\ResourceModel\Menu $resource = null,
        \Ves\Megamenu\Model\ResourceModel\Menu\Collection $resourceCollection = null,
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
        $this->_init('Ves\Megamenu\Model\ResourceModel\Menu');
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

    public function getMobileTemplates()
    {
        return [
            0 => __('Off Canvas Left'),
            1 => __('Accordion Menu'),
            2 => __('Custom Menu'),
            3 => __('Drill Down Menu')
        ];
    }

    public function getDesktopTemplates()
    {
        return [
            'vertical-left'  => __('Vertical Left Menu'),
            'vertical-right' => __('Vertical Right Menu'),
            'horizontal'     => __('Horizontal Menu'),
            'accordion'      => __('Accordion Menu'),
            'drill'          => __('Drill Down Menu')
        ];
    }

    public function getEventType()
    {
        return [
            'hover' => __('Hover'),
            'click' => __('Click')
        ];
    }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if($this->_store){
            return $this->_storeManager->getStore();
        }else{
            return false;
        }
    }

    public function setLoggedCustomerGroupId($customer_group_id = 0)
    {
        $this->_logged_customer_group_id = $customer_group_id;
        return $this;
    }

    public function getLoggedCustomerGroupId()
    {
        if($this->_logged_customer_group_id){
            return $this->_logged_customer_group_id;
        }else{
            return 0;
        }
    }

    /**
     * Load object data
     *
     * @param integer $modelId
     * @param null|string $field
     * @return $this
     */
    public function load($modelId, $field = null)
    {
        $this->_beforeLoad($modelId, $field);
        $store = $this->getStore();
        $customer_group_id = $this->getLoggedCustomerGroupId();
        $this->_getResource()->setStore($store)
                            ->setLoggedCustomerGroupId($customer_group_id)
                            ->load($this, $modelId, $field);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        $this->updateStoredData();
        return $this;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getAlias()];
    }

    /**
     * Synchronize object's stored data with the actual data
     *
     * @return $this
     */
    private function updateStoredData()
    {
        if (isset($this->_data)) {
            $this->storedData = $this->_data;
        } else {
            $this->storedData = [];
        }
        return $this;
    }
    public function getStoresIds(){
        return $this->_getResource()->lookupStoreIds($this->getId());
    }

    public function generateMenuTree(){
        //getnerate menu
        if($this->getId()){
            //Write code at here
            $tree_array = [];
            $data = $this->menuHelper;
            $menuItems  = $this->getMenuItems();
            $structure  = json_decode($this->getStructure(), true);
            $categories = [];
            foreach ($menuItems as $item) {
                if (isset($item['link_type']) && $item['link_type'] == 'category_link' && isset($item['category']) && !in_array($item['category'], $categories)) {
                    $categories[] = $item['category'];
                }
            }
            $data->setMenuCategories($categories);
            if (is_array($structure)) {
                $i = 1;
                foreach ($structure as $k => $v) {
                    $itemData = $data->renderMenuItemData($v, [], $menuItems);
                    $tree_array[] = $data->drawItemForTree($itemData, 0, 1, true, $i);
                    $i++;
                }
            }
            if($tree_array){
                $this->setMenuTree($tree_array);
            }
        }
        return $this;
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
        return $this->setData(self::MENU_ID, (int)$menuId);
    }

    /**
     * Get alias
     * @return string|null
     */
    public function getAlias(){
        return $this->getData(self::ALIAS);
    }

    /**
     * Set alias
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias){
        return $this->setData(self::ALIAS, $alias);
    }

    /**
     * Get mobile_template
     * @return string|null
     */
    public function getMobileTemplate(){
        return $this->getData(self::MOBILE_TEMPLATE);
    }

    /**
     * Set mobile_template
     * @param string $mobile_template
     * @return $this
     */
    public function setMobileTemplate($mobile_template){
        return $this->setData(self::MOBILE_TEMPLATE, $mobile_template);
    }

    /**
     * Get structure
     * @return string|null
     */
    public function getStructure(){
        return $this->getData(self::STRUCTURE);
    }

    /**
     * Set structure
     * @param string $structure
     * @return $this
     */
    public function setStructure($structure){
        return $this->setData(self::STRUCTURE, $structure);
    }

    /**
     * Get disable_bellow
     * @return string|null
     */
    public function getDisableBellow(){
        return $this->getData(self::DISABLE_BELLOW);
    }

    /**
     * Set disable_bellow
     * @param string $disable_bellow
     * @return $this
     */
    public function setDisableBellow($disable_bellow){
        return $this->setData(self::DISABLE_BELLOW, $disable_bellow);
    }

    /**
     * Get creation_time
     * @return string|null
     */
    public function getCretionTime(){
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Set creation_time
     * @param string $creation_time
     * @return $this
     */
    public function setCretionTime($creation_time){
        return $this->setData(self::CREATION_TIME, $disable_bellow);
    }

    /**
     * Get update_time
     * @return string|null
     */
    public function getUpdateTime(){
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Set update_time
     * @param string $update_time
     * @return $this
     */
    public function setUpdateTime($update_time){
        return $this->setData(self::UPDATE_TIME, $update_time);
    }

    /**
     * Get desktop_template
     * @return string|null
     */
    public function getDesktopTemplate(){
        return $this->getData(self::DESKTOP_TEMPLATE);
    }

    /**
     * Set desktop_template
     * @param string $desktop_template
     * @return $this
     */
    public function setDesktopTemplate($desktop_template){
        return $this->setData(self::DESKTOP_TEMPLATE, $desktop_template);
    }

     /**
     * Get design
     * @return string|null
     */
    public function getDesign(){
        return $this->getData(self::DESIGN);
    }

    /**
     * Set design
     * @param string $design
     * @return $this
     */
    public function setDesign($design){
        return $this->setData(self::DESIGN, $design);
    }

     /**
     * Get params
     * @return string|null
     */
    public function getParams(){
        return $this->getData(self::PARAMS);
    }

    /**
     * Set params
     * @param string $params
     * @return $this
     */
    public function setParams($params){
        return $this->setData(self::PARAMS, $params);
    }

    /**
     * Get disable_iblocks
     * @return string|null
     */
    public function getDisableIblocks(){
        return $this->getData(self::DISABLE_IBLOCKS);
    }

    /**
     * Set disable_iblocks
     * @param string $disable_iblocks
     * @return $this
     */
    public function setDisableIblocks($disable_iblocks){
        return $this->setData(self::DISABLE_IBLOCKS, $disable_iblocks);
    }

    /**
     * Get event
     * @return string|null
     */
    public function getEvent(){
        return $this->getData(self::EVENT);
    }

    /**
     * Set event
     * @param string $event
     * @return $this
     */
    public function setEvent($event){
        return $this->setData(self::EVENT, $event);
    }

    /**
     * Get classes
     * @return string|null
     */
    public function getClasses(){
        return $this->getData(self::CLASSES);
    }

    /**
     * Set classes
     * @param string $classes
     * @return $this
     */
    public function setClasses($classes){
        return $this->setData(self::CLASSES, $classes);
    }

    /**
     * Get width
     * @return string|null
     */
    public function getWidth(){
        return $this->getData(self::WIDTH);
    }

    /**
     * Set width
     * @param string $width
     * @return $this
     */
    public function setWidth($width){
        return $this->setData(self::WIDTH, $width);
    }

    /**
     * Get scrolltofix
     * @return string|null
     */
    public function getScrolltofix(){
        return $this->getData(self::SCROLLTOFIX);
    }

    /**
     * Set scrolltofix
     * @param string $scrolltofix
     * @return $this
     */
    public function setScrolltofix($scrolltofix){
        return $this->setData(self::SCROLLTOFIX, $scrolltofix);
    }

    /**
     * Get current_version
     * @return string|null
     */
    public function getCurrentVersion(){
        return $this->getData(self::CURRENT_VERSION);
    }

    /**
     * Set current_version
     * @param string $current_version
     * @return $this
     */
    public function setCurrentVersion($current_version){
        return $this->setData(self::CURRENT_VERSION, $current_version);
    }

    /**
     * Get mobile_menu_alias
     * @return string|null
     */
    public function getMobileMenuAlias(){
        return $this->getData(self::MOBILE_MENU_ALIAS);
    }

    /**
     * Set mobile_menu_alias
     * @param string $mobile_menu_alias
     * @return $this
     */
    public function setMobileMenuAlias($mobile_menu_alias){
        return $this->setData(self::MOBILE_MENU_ALIAS, $mobile_menu_alias);
    }

    /**
     * Get menu items
     * @return string|null
     */
    public function getMenuItems(){
        return $this->getData(self::MENU_ITEMS);
    }

    /**
     * Set menu_items
     * @param array $menu_items
     * @return $this
     */
    public function setMenuItems($menu_items){
        return $this->setData(self::MENU_ITEMS, $menu_items);
    }

    /**
     * Get customer_group_ids ids
     * @return mixed|null
     */
    public function getCustomerGroupIds(){
        return $this->getData(self::CUSTOMER_GROUP_IDS);
    }

    /**
     * Set customer_group_ids
     * @param mixed $customer_group_ids
     * @return $this
     */
    public function setCustomerGroupIds($customer_group_ids){
        return $this->setData(self::CUSTOMER_GROUP_IDS, $customer_group_ids);
    }

    /**
     * Get version_id
     * @return mixed|null
     */
    public function getVersionId(){
        return $this->getData(self::VERSION_ID);
    }

    /**
     * Set version_id
     * @param mixed $version_id
     * @return $this
     */
    public function setVersionId($version_id){
        return $this->setData(self::VERSION_ID, $version_id);
    }

    /**
     * Get store_id
     * @return mixed|null
     */
    public function getStoreId(){
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set store_id
     * @param mixed $store_id
     * @return $this
     */
    public function setStoreId($store_id){
        return $this->setData(self::STORE_ID, $store_id);
    }

    /**
     * Get menu_tree
     * @return mixed|null
     */
    public function getMenuTree(){
        return $this->getData(self::MENU_TREE);
    }

    /**
     * Set menu_tree
     * @param mixed $menu_tree
     * @return $this
     */
    public function setMenuTree($menu_tree){
        return $this->setData(self::MENU_TREE, $menu_tree);
    }

    /**
     * Get design_decode
     * @return mixed|null
     */
    public function getDesignDecode(){
        if(!$this->hasData(self::DESIGN_DECODE)){
            $design = $this->getDesign();
            $design_decode = "";
            if($design){
                if (!is_array($design)) {
                    $design_decode = unserialize($design);
                }else{
                    $design_decode = $design;
                }
            }
            $design_decode = json_encode($design_decode);
            $this->setDesignDecode($design_decode);
        }
        return $this->getData(self::DESIGN_DECODE);
    }

    /**
     * Set design_decode
     * @param mixed $design_decode
     * @return $this
     */
    public function setDesignDecode($design_decode){
        return $this->setData(self::DESIGN_DECODE, $design_decode);
    }

    /**
     * Get revert_next
     * @return mixed|null
     */
    public function getRevertNext(){
        return $this->getData(self::REVERT_NEXT);
    }

    /**
     * Set revert_next
     * @param mixed $revert_next
     * @return $this
     */
    public function setRevertNext($revert_next){
        return $this->setData(self::REVERT_NEXT, $revert_next);
    }

    /**
     * Get revert_previous
     * @return mixed|null
     */
    public function getRevertPrevious(){
        return $this->getData(self::REVERT_PREVIOUS);
    }

    /**
     * Set revert_previous
     * @param mixed $revert_previous
     * @return $this
     */
    public function setRevertPrevious($revert_previous){
        return $this->setData(self::REVERT_PREVIOUS, $revert_previous);
    }
}