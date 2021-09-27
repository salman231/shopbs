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

namespace Webkul\MobikulCore\Block\Adminhtml\Notification\Category\Checkbox;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\App\ObjectManager;

/**
 * Class Tree block
 */
class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    protected $_selectedIds  = [];
    protected $_expandedPath = [];

    /**
     * Function prepare layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate("notification/category/checkbox/tree.phtml");
    }

    /**
     * Function to get gategory Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        $notificationId = ObjectManager::getInstance()->get(\Magento\Framework\App\Request\Http::class)->getParam("id");
        if ($notificationId) {
            $notification = ObjectManager::getInstance()
                ->create(\Webkul\MobikulCore\Api\NotificationRepositoryInterface::class)
                ->getById($notificationId);
            $notificationData = $notification->getData();
            $serializer = ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Serialize::class);
            $filterData = $serializer->unserialize($notificationData["filter_data"]);
            $categoryIds = explode(",", $filterData["category_ids"]);
            $this->_selectedIds = $categoryIds;
        }
        return $this->_selectedIds;
    }

    /**
     * Function to get id string
     *
     * @return int
     */
    public function getIdsString()
    {
        return implode(",", $this->_selectedIds);
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
     * @return object
     */
    protected function setExpandedPath($path)
    {
        $this->_expandedPath = array_merge($this->_expandedPath, explode("/", $path));
        return $this;
    }

    /**
     * Function to get node jSon
     *
     * @return array
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
