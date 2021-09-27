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

namespace Webkul\MobikulCore\Controller\Adminhtml\Categoryimages;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index for Categoryimages
 */
class Index extends \Webkul\MobikulCore\Controller\Adminhtml\Categoryimages
{
    /**
     * Execute function for class Index
     *
     * @return resultFactory
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::categoryimages");
        $resultPage->getConfig()->getTitle()->prepend(__("Manage Category's Banners and Icons"));
        return $resultPage;
    }

    /**
     * Function to check if the controller is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::categoryimages");
    }
}
