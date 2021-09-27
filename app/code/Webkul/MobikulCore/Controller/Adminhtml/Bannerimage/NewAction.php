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

namespace Webkul\MobikulCore\Controller\Adminhtml\Bannerimage;

/**
 * Class NewAction controller
 */
class NewAction extends \Webkul\MobikulCore\Controller\Adminhtml\Bannerimage
{
    /**
     * Execure function for mass delete class
     *
     * @return page
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward("edit");
        return $resultForward;
    }

    /**
     * Function to check is class is allowed to be accessed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::bannerimage");
    }
}
