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

namespace Ves\Megamenu\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\CacheInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;

class StoreCategories extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    const CATEGORY_TREE_ID = 'VES_MEGAMENU_CATEGORY_TREE';
    const MENU_CACHE_TAG = 'menu_editor_categories';
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var array
     */
    protected $categoriesTrees = [];

    protected $categoriesCollection = [];

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var DbHelper
     */
    protected $dbHelper;

    protected $list = [];

    /**
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param CategoryCollectionFactory               $categoryCollectionFactory
     * @param DbHelper                                $dbHelper
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        DbHelper $dbHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->dbHelper                  = $dbHelper;
        $this->_layout                   = $layout;
        $this->_storeManager             = $storeManager;

    }//end __construct()

    /**
     * Retrieve cache interface
     *
     * @return CacheInterface
     * @deprecated
     */
    private function getCacheManager()
    {
        if (!$this->cacheManager) {
            $this->cacheManager = ObjectManager::getInstance()
                ->get(CacheInterface::class);
        }
        return $this->cacheManager;
    }

     /**
     * Get category collection
     *
     * @param bool $isActive
     * @param bool|int $level
     * @param bool|string $sortBy
     * @param bool|int $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection or array
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = 'position', $pageSize = false)
    {
        $collection = $this->categoryCollectionFactory->create();
        //$collection->addAttributeToSelect('*');        
        
        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
                
        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }
        
        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }
        
        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize); 
        }    
        
        return $collection;
    }
    

    /**
     * Retrieve categories tree
     *
     * @param  string|null $filter
     * @return array
     */
    public function getCategoriesTree($filter = null)
    {
        if (isset($this->categoriesTrees[$filter])) {
            return $this->categoriesTrees[$filter];
        }

        // @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection
        $collection = $this->getCategoryCollection();

        if (!empty($filter)) {
           $collection->addAttributeToFilter('entity_id', ['in' => $this->getCategoryIdsByName(null,$filter)]);
        }
        $collection->addAttributeToSelect(['name', 'is_active', 'parent_id']);

        $categoryById = [
                         CategoryModel::TREE_ROOT_ID => [
                                                         'value'    => CategoryModel::TREE_ROOT_ID,
                                                         'children' => null,
                                                        ],
                        ];

        foreach ($collection as $category) {
            if($category->getName()) {
                foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                    if (!isset($categoryById[$categoryId])) {
                        $categoryById[$categoryId] = ['value' => $categoryId, 'category' => $categoryId, 'link_type' => 'category_link'];
                    }
                }
                $categoryById[$category->getId()]['category'] = $category->getId();
                $categoryById[$category->getId()]['link_type'] = "category_link";
                $categoryById[$category->getId()]['is_active']        = $category->getIsActive();
                $categoryById[$category->getId()]['label']            = str_replace("'", " ", $category->getName());
                $categoryById[$category->getId()]['name']             = str_replace("'", " ", $category->getName());
                $categoryById[$category->getParentId()]['children'][] = &$categoryById[$category->getId()];
                $categoryById[$category->getId()]['level']            = $category->getLevel();
            }
        }

        $this->categoriesTrees[$filter] = $categoryById[CategoryModel::TREE_ROOT_ID]['children'];
        return $this->categoriesTrees[$filter];

    }//end getCategoriesTree()

    /**
     * Looks like this method can be safely removed
     * @param $menuCategories
     * @param $filter
     * @param $_store_id
     * @return array
     */
    private function getCategoryIdsByName($menuCategories = [], $filter = null, $_store_id = null)
    {
        // @var $matchingNamesCollection \Magento\Catalog\Model\ResourceModel\Category\Collection
        $matchingNamesCollection = $this->getCategoryCollection();
        if ($filter !== null) {
            $matchingNamesCollection->addAttributeToFilter(
                'name',
                ['like' => $this->dbHelper->addLikeEscape($filter, ['position' => 'any'])]
            );
        }

        $matchingNamesCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID]);

        if($_store_id !== null ) {
            $matchingNamesCollection->setStoreId($_store_id);
        }
        if (!empty($menuCategories)) {
            $matchingNamesCollection->addAttributeToFilter('entity_id', ['in' => $menuCategories]);
        }
        $shownCategoriesIds = [];

        /*
            * @var \Magento\Catalog\Model\Category $category
        */
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }
        return array_keys($shownCategoriesIds);
    }

    public function getCategoriesCollection($menuCategories = [], $filter = null, $_store_id = null)
    {
        krsort($menuCategories);
        $cache_cat_key = implode("_", $menuCategories);
        $categoryTree = $this->getCacheManager()->load(self::CATEGORY_TREE_ID .'_'.$cache_cat_key. '_' . $filter.'_'.$_store_id);

        if ($categoryTree) {
            return unserialize($categoryTree);
        }

        $storeId = $_store_id;
        if($storeId === null){
            $storeId = $this->_storeManager->getStore()->getId();
        }

        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection = $this->getCategoryCollection();

        $collection->addAttributeToFilter('entity_id', ['in' => $this->getCategoryIdsByName($menuCategories, $filter, $storeId)])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
            ->setStoreId($storeId);

        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID,
                'children' => null,
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }
            $categoryById[$category->getId()]['category']['url']  = $category->setStoreId($_store_id)->getUrl();
            $categoryById[$category->getId()]['category']['name'] = $category->getName();
            $categoryById[$category->getParentId()]['children'][] = &$categoryById[$category->getId()];
        }

        $this->getCacheManager()->save(
            serialize($categoryById),
            self::CATEGORY_TREE_ID .'_' . $cache_cat_key.'_' . $filter.'_'.$_store_id,
            [
                \Magento\Catalog\Model\Category::CACHE_TAG,
                \Magento\Framework\App\Cache\Type\Block::CACHE_TAG,
                self::MENU_CACHE_TAG
            ]
        );

        return $categoryById;

    }//end getCategoriesTree()


    public function generatCategory($category) {
        $this->list[] = $category;
        if (isset($category['children']) && $category['children']) {
            foreach ($category['children'] as $cat) {
                $this->generatCategory($cat);
            }
        }
    }

    public function getCategoryList($root_id = 0) {
        $categoryList = $this->getCacheManager()->load(self::CATEGORY_TREE_ID .'_BACKEND_MENU_EDITOR_ROOT'.$root_id);
        if ($categoryList) {
            return unserialize($categoryList);
        }
        $categoriesTrees = $this->getCategoriesTree();
        if($categoriesTrees) {
            foreach ($categoriesTrees as $category) {
                $this->generatCategory($category);
            }
        }
        $list = $this->list; 
        foreach ($list as $k => &$category) {
            if ($category['level'] == 0) {
                unset($list[$k]);
            }
            $category['level'] -= 1;

            $categoryLabel = '';

            if ($category['level']!=0) {
                $categoryLabel = '| ';
            }

            $categoryLabel .= $this->_getSpaces($category['level']) . '(ID:' . $category['value'] . ') ' . $category['label'];
            $category['label'] = $categoryLabel;
        }
        $this->getCacheManager()->save(
            serialize($list),
            self::CATEGORY_TREE_ID .'_BACKEND_MENU_EDITOR_ROOT'.$root_id,
            [
                \Magento\Catalog\Model\Category::CACHE_TAG,
                \Magento\Framework\App\Cache\Type\Block::CACHE_TAG,
                self::MENU_CACHE_TAG
            ],
            3600
        );
        return $list;
    }

    protected function _getSpaces($n)
    {
        $s = '';
        for($i = 0; $i < $n; $i++) {
            $s .= '_ _ ';
        }
        return $s;
    }


}//end class
