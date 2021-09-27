<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Category;

/**
 * Class Tree model
 */
class Tree extends \Magento\Catalog\Model\Category\Tree
{
    protected function getChildren($node, $depth, $currentLevel)
    {
        if ($node->hasChildren()) {
            $children = [];
            foreach ($node->getChildren() as $child) {
                if ($depth !== null && $depth <= $currentLevel) {
                    break;
                }
                if ($child->getIsActive()) {
                    $children[] = $this->getTree($child, $depth, $currentLevel + 1);
                }
            }
            return $children;
        }
        return [];
    }

    public function getTree($node, $depth = null, $currentLevel = 0)
    {
        $children = $this->getChildren($node, $depth, $currentLevel);
        $tree     = $this->treeFactory->create()
            ->setCategoryId($node->getId())
            ->setName($node->getName())
            ->setChildren($children);
        return $tree;
    }

    protected function prepareCollection()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->categoryCollection
            ->addAttributeToSelect("name")
            ->addAttributeToSelect("is_active")
            ->addAttributeToSelect("include_in_menu")
            ->addAttributeToFilter("include_in_menu", 1)
            ->addAttributeToFilter("is_active", 1)
            ->setProductStoreId($storeId)
            ->setLoadProductCount(true)
            ->setStoreId($storeId);
    }
}
