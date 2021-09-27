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
 * Class Index for Carousel
 */
class Index extends \Webkul\MobikulCore\Controller\Adminhtml\Carousel
{
    /**
     * Execute Fucntion
     *
     * @return jSon
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::carousel");
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Carousel"));
        return $resultPage;
    }

    /**
     * Fucntion to check if this controller can be accessed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::carousel");
    }
}
