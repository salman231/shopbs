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

namespace Webkul\MobikulCore\Controller\Adminhtml\AppCreator;

/**
 * Class Index for AppCreator
 */
class Index extends \Webkul\MobikulCore\Controller\Adminhtml\AppCreator
{

    /**
     * Execute Fucntion
     *
     * @return jSon
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Webkul_MobikulCore::appcreator");
        $resultPage->getConfig()->getTitle()->prepend(__("Home Page Configurator"));
        return $resultPage;
    }

    /**
     * Fucntion to check if this controller can be accessed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::appcreator");
    }
}
