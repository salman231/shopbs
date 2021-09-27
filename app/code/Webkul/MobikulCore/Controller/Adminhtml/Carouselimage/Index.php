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
 * Class Index for Carouselimage
 */
class Index extends \Webkul\MobikulCore\Controller\Adminhtml\Carouselimage
{
    /**
     * Execute Fucntion for Class Edit
     *
     * @return page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::carouselimage");
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Carousel Images"));
        return $resultPage;
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
