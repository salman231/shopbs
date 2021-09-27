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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Categoryimages;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\App\ObjectManager;

/**
 * Class Category block
 */
class Category extends \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree
{
    protected $_selectedIds  = [];
    protected $_expandedPath = [];

    /**
     * Function to get CategoryId
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate("categoryimages/categories.phtml");
    }

    /**
     * Function to get CategoryId
     *
     * @return null|int
     */
    public function getCategoryId()
    {
        return ObjectManager::getInstance()->get(\Magento\Framework\Registry::class)->registry("categoryId") ? : null;
    }

    /**
     * Function to get CategoryIds
     *
     * @return array
     */
    public function getCategoryIds()
    {
        $categoryId = ObjectManager::getInstance()->get(\Magento\Framework\Registry::class)->registry("categoryId");
        $this->_selectedIds = [$categoryId];
        return $this->_selectedIds;
    }
}
