<?php
 
namespace Webkul\GiftCard\Observer;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
 
/**
 * Add Weee item to Payment Cart amount.
 */
class AddCustomAmountItem implements ObserverInterface
{
    public $checkout;

    public function __construct(Session $checkout)
    {
        $this->checkout = $checkout;
    }
    /**
     * Add custom amount as custom item to payment cart totals.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Payment\Model\Cart $cart */
        $cart = $observer->getEvent()->getCart();
        $quote = $this->checkout->getQuote();
       
        $address = $quote->getIsVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        if ($customAmount = $quote->getFee()) {
            $cart->addCustomItem(__('Gift Coupon Code'), 1, -1.00 * -$quote->getFee(), 'gift_coupon_code');
        }
    }
}
