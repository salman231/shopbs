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

namespace Webkul\MobikulCore\Model\ResourceModel\Category;

/**
 * Class Tree resourceModel
 */
class Tree extends \Magento\Catalog\Model\ResourceModel\Category\Tree
{
    public function addCollectionData(
        $collection = null,
        $sorted = false,
        $exclude = [],
        $toLoad = true,
        $onlyActive = false
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($objectManager->get(\Magento\Framework\App\Request\Http::class)->getModuleName() == "mobikul") {
            return parent::addCollectionData($collection, $sorted, $exclude, $toLoad, false);
        }
        return parent::addCollectionData($collection, $sorted, $exclude, $toLoad, $onlyActive);
    }
}
