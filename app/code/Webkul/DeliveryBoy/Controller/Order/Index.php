<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Controller\Customer as CustomerController;

class Index extends CustomerController
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if ($navigationBlock = $resultPage->getLayout()->getBlock("customer_account_navigation")) {
            $navigationBlock->setActive("expressdelivery/order");
        }
        if ($block = $resultPage->getLayout()->getBlock("express_order_list")) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        $resultPage->getConfig()->getTitle()->set(__("My Express Orders"));
        return $resultPage;
    }
}
