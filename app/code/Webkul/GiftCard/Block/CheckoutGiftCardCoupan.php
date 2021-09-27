<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Block;

use Magento\Store\Model\Store;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * GiftCatrd block.
 *
 * @author Webkul Software
 */
class CheckoutGiftCardCoupan extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\GiftCard\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;
    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $_registry
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param \Magento\Framework\App\Http\Context              $httpContext
     * @param \Webkul\GiftCard\Helper\Data                     $helperData
     * @param \Magento\Catalog\Model\ProductFactory            $product
     * @param DateTime                                         $date
     * @param Store                                            $store
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $_registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Http\Context $httpContext,
        \Webkul\GiftCard\Helper\Data $helperData,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $product,
        DateTime $date,
        \Magento\Checkout\Model\Session $checkoutSession,
        Store $store,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->_registry = $_registry;
        $this->_helperData = $helperData;
        $this->_objectManager = $objectManager;
        $this->_session = $context->getStoreManager();
        $this->_product = $product;
        parent::__construct($context, $data);
        $this->_httpContext = $httpContext;
    }

    /**
     * get storeAvilability that weather it is,
     * enable or disable from configuration.
     *
     * @return int
     */
    public function isCustomerLoggrdIn()
    {
        return  $this->_helperData->isCustomerLoggrdIn();
    }

    /**
     * Get the value of coupan price and code stored in session
     *
     * @return array
     */
    public function getSessionDataOfCoupon()
    {
        $discountTotal = 0;
        $couponCode = $this->cart->getQuote()->getCouponCode();
        $couponData = ['code'=>"",'price'=>""];
        $quote = $this->checkoutSession->getQuote();
        foreach ($quote->getAllItems() as $item) {
            $discountTotal += $item->getDiscountAmount();
        }
        if (isset($couponCode)) {
            $couponData['code'] = $couponCode;
        }
        if (isset($discountTotal) && ($discountTotal != 0)) {
            $couponData['price'] = $discountTotal;
        }
        return $couponData;
    }
    /**
     * get Product
     *
     * @return Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $id = $this->getRequest()->getParam('id');
        $product = $this->_product->create()->load($id);
        ;
        return $product;
    }
    public function getproductType()
    {
        return $this->getProduct()->getTypeID();
    }

    public function getQuoteData()
    {
        $quoteData = [];
        $quote = $this->cart->getQuote();
        $quoteData['gift_code'] = $quote->getGiftCode();
        $amount = explode('-', $quote->getFee());
        if (isset($amount[1])) {
            $quoteData['fee'] = $amount[1];
        } else {
            $quoteData['fee'] = '';
        }
        return $quoteData;
    }
}
