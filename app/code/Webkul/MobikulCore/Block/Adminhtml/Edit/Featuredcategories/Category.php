<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Featuredcategories;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\App\ObjectManager;

/**
 * Class Category block
 */
class Category extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    protected $_selectedIds  = [];
    protected $_expandedPath = [];

    /**
     * Function to prepare layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate("featuredcategories/categories.phtml");
    }

    /**
     * Function t get category Id
     *
     * @return int|null
     */
    public function getCategoryId()
    {
        return ObjectManager::getInstance()->get(\Magento\Framework\Registry::class)->registry("categoryId") ? : null;
    }

    /**
     * Function to get category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        $categoryId = ObjectManager::getInstance()->get(\Magento\Framework\Registry::class)->registry("categoryId");
        $this->_selectedIds = [$categoryId];
        return $this->_selectedIds;
    }

    /**
     * Function to set category Ids
     *
     * @return object
     */
    public function setCategoryIds($ids)
    {
        if (empty($ids)) {
            $ids = [];
        } elseif (!is_array($ids)) {
            $ids = [(int)$ids];
        }
        $this->_selectedIds = $ids;
        return $this;
    }

    /**
     * Function to get expanded path
     *
     * @return string
     */
    protected function getExpandedPath()
    {
        return $this->_expandedPath;
    }

    /**
     * Function to set expanded path
     *
     * @param string $path path
     *
     * @return object
     */
    protected function setExpandedPath($path)
    {
        $this->_expandedPath = array_merge($this->_expandedPath, explode("/", $path));
        return $this;
    }

    /**
     * Function to get Node Json
     *
     * @param node $node  node
     * @param int  $level level
     *
     * @return array $item
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = [];
        $item["text"] = $this->escapeHtml($node->getName());
        if ($this->_withProductCount) {
            $item["text"] .= " (" . $node->getProductCount() . ")";
        }
        $item["id"] = $node->getId();
        $item["path"] = $node->getData("path");
        $item["cls"] = "folder " . ($node->getIsActive() ? "active-category" : "no-active-category");
        $item["allowDrop"] = false;
        $item["allowDrag"] = false;
        if (in_array($node->getId(), $this->getCategoryIds())) {
            $this->setExpandedPath($node->getData("path"));
            $item["checked"] = true;
        }
        if ($node->getLevel() < 2) {
            $this->setExpandedPath($node->getData("path"));
        }
        if ($node->hasChildren()) {
            $item["children"] = [];
            foreach ($node->getChildren() as $child) {
                $item["children"][] = $this->_getNodeJson($child, $level + 1);
            }
        }
        if (empty($item["children"]) && (int)$node->getChildrenCount() > 0) {
            $item["children"] = [];
        }
        $item["expanded"] = in_array($node->getId(), $this->getExpandedPath());
        return $item;
    }
}
