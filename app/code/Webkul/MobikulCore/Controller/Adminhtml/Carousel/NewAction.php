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

namespace Webkul\MobikulCore\Controller\Adminhtml\Carousel;

/**
 * Class NewAction for Carousel
 */
class NewAction extends \Webkul\MobikulCore\Controller\Adminhtml\Carousel
{
    /**
     * Execute Function for Class NewAction
     *
     * @return jSon
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward("edit");
        return $resultForward;
    }

    /**
     * Function to check if the action is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::carousel");
    }
}
