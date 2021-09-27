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

namespace Webkul\MobikulCore\Block\Adminhtml;

/**
 * Class Thumbnail block
 */
class Thumbnail extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Function render
     *
     * @param \Magento\Framework\DataObject $row row
     *
     * @return html
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $target = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $imageUrl = $target.$row->getFilename();
        $html  = '<img style="border:1px solid #d6d6d6;width:50px" src="'.$imageUrl.'"/>';
        return $html;
    }
}
