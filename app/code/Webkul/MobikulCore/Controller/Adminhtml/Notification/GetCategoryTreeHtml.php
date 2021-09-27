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

namespace Webkul\MobikulCore\Controller\Adminhtml\Notification;

/**
 * GetCategoryTreeHtml Class controller
 */
class GetCategoryTreeHtml extends \Webkul\MobikulCore\Controller\Adminhtml\Notification
{
    public function execute()
    {
        $block = $this->view->getLayout()->createBlock(
            \Webkul\MobikulCore\Block\Adminhtml\Notification\Category\Checkbox\Tree::class
        );
        $this->getResponse()->setBody($block->toHtml());
    }
}
