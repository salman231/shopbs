<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magemonkey\Chargeanywhere\Model;
use Magento\Framework\Simplexml\Element;
use Magento\Sales\Model\Order\Payment\Transaction;


/**
 * Pay In Store payment method model
 */
class Chargeanywhere extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'chargeanywhere';
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Availability option
     *
     * @var bool
     */
    // protected $_isOffline = true;

    protected $_canAuthorize = true;
    protected $_canCapture = true;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\State $state,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManager $sessionManager,
        \Magento\Directory\Model\Region $regionList,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magemonkey\Chargeanywhere\Helper\Data $helperData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magemonkey\Chargeanywhere\Logger\Logger $magelogger,    
        array $data = array()
    ) {
        $this->quote = $quote;
        $this->_session = $quoteSession;
        $this->_appState = $state;
        $this->_customerSession = $customerSession;
        $this->_regionList = $regionList;
        $this->request = $request;
        $this->_sessionId = $sessionManager->getSessionId();
        $this->_sessionManager = $sessionManager;
        $this->transactionBuilder = $transactionBuilder;
        $this->helper = $helperData;
        $this->_checkoutSession = $checkoutSession;
        $this->magelogger = $magelogger;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
            
        );
        
        $this->cart = $cart;
        $this->_countryFactory = $countryFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate() {
         parent::validate();
        // else parent::validate();
        return true;

    }
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount) {
        // $order     =  $payment->getOrder();
        $chargedata = $this->_checkoutSession->getChargeData();
        $payment->setTransactionId($chargedata['ReferenceNumber']);
        $payment->setParentTransactionId($chargedata['ReferenceNumber']);
        $payment->setIsTransactionClosed(0);
        return $this;
    }
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {   $chargedata = $this->_checkoutSession->getChargeData();
        $payment->setLastTransId($chargedata['ReferenceNumber']);
        $payment->setTransactionId($chargedata['ReferenceNumber']);
        $payment->setIsTransactionClosed(1);
        return $this;
    }

    public function getConfigPaymentAction()
    {
        $chargedata = $this->_checkoutSession->getChargeData();
        /*$this->magelogger->info(print_r($chargedata,true));
        $this->magelogger->info(print_r($this->getConfigData('payment_action'),true));*/
        $protype = array('giftcard', 'Membership','downloadable','virtual');
        if(in_array($chargedata['product_type'], $protype)){
            return 'authorize_capture';
        }else{
            return $this->getConfigData('payment_action');
        }
        
    }   
}
