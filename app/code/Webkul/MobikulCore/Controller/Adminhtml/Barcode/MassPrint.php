<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_Pos
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Controller\Adminhtml\Barcode;

use Magento\Backend\App\Action\Context;

class MassPrint extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Catalog::catalog");
        $resultPage->addBreadcrumb(__("Mobikul"), __("Barcode"));
        $resultPage->getConfig()->getTitle()->prepend(__("Product Barcodes"));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_MobikulCore::barcode");
    }
}
