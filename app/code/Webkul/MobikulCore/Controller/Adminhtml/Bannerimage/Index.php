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
 * Class Index for Banner Imgaes
 */
class Index extends \Webkul\MobikulCore\Controller\Adminhtml\Bannerimage
{
    /**
     * Execute Function for Index class
     *
     * @return page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::bannerimage");
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Banner"));
        return $resultPage;
    }

    /**
     * Function to check if page is allowed to view.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::bannerimage");
    }
}
