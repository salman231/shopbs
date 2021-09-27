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

namespace Webkul\MobikulCore\Controller\Adminhtml\Carouselimage;

/**
 * Class NewAction for Carouselimage
 */
class NewAction extends \Webkul\MobikulCore\Controller\Adminhtml\Carouselimage
{
    /**
     * Execute Fucntion for Class NewAction
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
     * Fucntion to check if the controller is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::carouselimage");
    }
}
