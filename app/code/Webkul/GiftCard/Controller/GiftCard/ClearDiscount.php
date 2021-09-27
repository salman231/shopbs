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
namespace Webkul\GiftCard\Controller\GiftCard;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul GiftCard ClearDiscount Controller.
 */
class ClearDiscount extends Action
{
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_salesRule;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_quote;
    
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_backendSession;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     *
     * @param Context                                            $context
     * @param \Magento\SalesRule\Model\Rule                      $salesRule
     * @param \Magento\Checkout\Model\Cart                       $quote
     * @param \Magento\Framework\Session\SessionManagerInterface $backendSession
     * @param \Magento\Checkout\Model\Session                    $checkoutSession
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\Rule $salesRule,
        \Magento\Checkout\Model\Cart $quote,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $backendSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Webkul\GiftCard\Helper\Data $helper
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->cart = $cart;
        $this->_salesRule = $salesRule;
        $this->_quote = $quote;
        $this->_backendSession = $backendSession;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $this->helper->clearGiftCode();
        $param=$this->getRequest()->getParams();
        $setMessages = false;
        if (isset($param['cartpage']) && $param['cartpage']) {
            $setMessages = true;
        }
        if ($setMessages) {
            $this->messageManager->addWarning(__("Gift Card Discount Removed."));
        } else {
            $response = [];
            $response['message'] = __("Gift Card Discount Removed.");
            $response['code'] = '1';
            $result->setData($response);
            return $result;
        }
    }
}
