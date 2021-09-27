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

namespace Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories;

/**
 * Class NewAction controller
 */
class NewAction extends \Webkul\MobikulCore\Controller\Adminhtml\Featuredcategories
{
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward("edit");
        return $resultForward;
    }
}
