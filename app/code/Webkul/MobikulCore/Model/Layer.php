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

namespace Webkul\MobikulCore\Model;

class Layer extends \Magento\Catalog\Model\Layer
{
    public $customCollection;

    public function getProductCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $wholeData = $objectManager->create(\Magento\Framework\App\Request\Http::class)->getPostValue();
        if (isset($wholeData["custom"]) && $wholeData["customCollection"] == 1) {
            $this->prepareProductCollection($this->customCollection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $this->customCollection;
            return $this->customCollection;
        } else {
            if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
                $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
            } else {
                $collection = $this->collectionProvider->getCollection($this->getCurrentCategory());
                $this->prepareProductCollection($collection);
                $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
            }
            return $collection;
        }
    }
}
