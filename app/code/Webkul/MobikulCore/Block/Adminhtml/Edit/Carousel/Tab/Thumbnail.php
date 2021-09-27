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

namespace Webkul\MobikulCore\Block\Adminhtml\Edit\Carousel\Tab;

/**
 * Class Thumbnail block
 */
class Thumbnail extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Function to Render data rows
     *
     * @param \Magento\Framework\DataObject $row row
     *
     * @return html
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $imageHelperFactory = $objectManager->create(\Magento\Catalog\Helper\ImageFactory::class);
        $imageUrl = $imageHelperFactory->create()->init($row, "product_thumbnail_image")->getUrl();
        $html  = '<img src="'.$imageUrl.'"/>';
        return $html;
    }
}
