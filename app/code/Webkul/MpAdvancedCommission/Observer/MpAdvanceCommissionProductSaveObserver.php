<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Observer;

use Magento\Framework\Event\ObserverInterface;

class MpAdvanceCommissionProductSaveObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getProduct();
        $_commissionPrice = $_product->getCommissionForProduct();
        try {
            if ($_commissionPrice < 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Commission Price For Product'));
            }
        } catch (\LocalizedException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }
}
