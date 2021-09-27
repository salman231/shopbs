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
use Magento\Framework\Filesystem\Driver\File;

/**
 * Webkul GiftCard Landing page UpdateGiftCard Controller.
 */
class UpdateGiftCard extends Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Webkul\GiftCard\Model\GiftUserFactory
     */
    protected $_giftuser;
    
    /**
     * @var \Webkul\GiftCard\Model\GiftDetailFactory
     */
    protected $_giftDetail;
    
    /**
     * @var \Webkul\GiftCard\Helper\Data
     */
    protected $_dataHelper;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
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
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\GiftCard\Model\GiftUserFactory $giftUser
     * @param \Webkul\GiftCard\Model\GiftDetailFactory $giftDetail
     * @param \Webkul\GiftCard\Helper\Data $dataHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\SalesRule\Model\Rule $salesRule
     * @param \Magento\Checkout\Model\Cart $quote
     * @param \Magento\Framework\Session\SessionManagerInterface $backendSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        File $file,
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\GiftCard\Model\GiftUserFactory $giftUser,
        \Webkul\GiftCard\Model\GiftDetailFactory $giftDetail,
        \Webkul\GiftCard\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\SalesRule\Model\Rule $salesRule,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Cart $quote,
        \Magento\Framework\Session\SessionManagerInterface $backendSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->file = $file;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_storeManager = $storeManager;
        $this->_giftuser = $giftUser;
        $this->_giftDetail = $giftDetail;
        $this->_dataHelper = $dataHelper;
        $this->_customerSession = $customerSession;
        $this->_salesRule = $salesRule;
        $this->_quote = $quote;
        $this->logger = $logger;
        $this->_backendSession = $backendSession;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $param=$this->getRequest()->getParams();
        $setMessages = false;
        if (isset($param['cartpage']) && $param['cartpage']) {
            $setMessages = true;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get(\Magento\Checkout\Model\Cart::class);
        $grandTotal = 0;
      
        foreach ($cart->getQuote()->getAllItems() as $item) {
          
            if ($item->getProductType() != "giftcard") {
                $grandTotal += $item->getPrice() * $item->getQty();
            }
        }
      
        if ($grandTotal == 0) {
            $response['message'] =__("Invalid Request");
            $response['code'] = '0';
            $result->setData($response);
            $this->setMessages($response, $setMessages);
            return $result;
        }
        if (empty($param['amount'])) {
            $request_body = $this->file->fileGetContents('php://input');
            $postData = explode('&', $request_body);
            $postAmt = explode('=', $postData[0]);
            $postCode = explode('=', $postData[1]);
            $param['amount'] =$postAmt[1];
            $param["code"] = $postCode[1];
        }
        $param["code"] = urldecode($param["code"]);
        $rates = $this->_dataHelper->getCurrentCurrencyRate();
        $price = ($grandTotal < $param['amount']) ? $grandTotal : $param['amount'];
        $price=$price/$rates;
        $param['amount']=$price;
        
        if ((real)$param['amount']>0) {
            $whom="";
            $collections=$this->_giftuser->create()->getCollection();
            $model = $collections->addFieldToFilter("code", $param["code"]);
            if ($model->getSize()) {
                $collectionData = $model->getData();
                
                $remainingamount = $model->getFirstItem()->getRemainingAmt();
                if ($param['amount'] > $remainingamount) {
                    $remainingamount = $this->_dataHelper->getCurrentCurrencySymbol().$remainingamount;
                    $response['message'] =__("You have only %1 amount remaining in your gift card", $remainingamount);
                    $response['code'] = '0';
                    $result->setData($response);
                    $this->setMessages($response, $setMessages);
                    return $result;
                }
                $customerEmail=$this->_customerSession->getCustomer()->getEmail();
                $userCheck =  $this->userCheck($collectionData, $customerEmail);
                if ($userCheck == 'error') {
                        $response['message'] = __('Invalid user');
                        $response['code'] = '0';
                        $result->setData($response);
                        $this->setMessages($response, $setMessages);
                        return $result;

                }
                $giftDetailModel = $this->_giftDetail->create()->load($model->getColumnValues('giftcodeid')[0]);
                $duration = $giftDetailModel->getDuration();
                $websiteIds = $giftDetailModel->getWebsiteIds();
                if ($websiteIds) {
                    $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
                    if (!in_array($currentWebsiteId, explode(',', $websiteIds))) {
                        $response['message'] = __("The gift code %1 is not available", $param['code']);
                        $response['code'] = '0';
                        $result->setData($response);
                        $this->setMessages($response, $setMessages);
                        return $result;
                    }
                }
                $isExpire = $this->_dataHelper->checkExpirationOfGiftCard(
                    $model->getColumnValues('alloted')[0],
                    $duration
                );
                $codeCheck = $this->codeCheck($isExpire, $model, $param);
                if ($codeCheck == 'apply') {
                    $response['message'] = __("The gift code %1 is expired", $param['code']);
                    $response['code'] = '0';
                    $result->setData($response);
                    $this->setMessages($response, $setMessages);
                    return $result;
                }

                if ($codeCheck == 'disable') {
                   
                    $response['message'] =
                    __("The gift code %1 is disable. Please contact administration.", $param['code']);
                    $response['code'] = '0';
                    $result->setData($response);
                    $this->setMessages($response, $setMessages);
                    return $result;
                }
                foreach ($model as $m) {
                        $whom=$m->getEmail();
                }
     
                $codeData = $this->codeApply($customerEmail, $param, $collections);
                if ($codeData == 'expire') {
                    $response['message'] =__("Gift code has been expired.");
                    $response['code'] = '0';
                    $result->setData($response);
                    $this->setMessages($response, $setMessages);
                    return $result;
                }
                if ($codeData == 'expire') {
                    $response['message'] =__("Please enter a valid amount");
                    $response['code'] = '0';
                    $result->setData($response);
                    $this->setMessages($response, $setMessages);
                    return $result;
                }
                if ($codeData == 'success') {
                    $response['message'] =__('Gift Card Discount Applied Successfully');
                    $response['code'] = '1';
                    $result->setData($response);
                    $this->setMessages($response, $setMessages);
                    return $result;
                }
                if ($codeData == 'valid') {
                    $response['message'] = __("The gift code %1 is not valid", $param['code']);
                    $response['code'] = '0';
                    $result->setData($response);
                    $this->setMessages($response, $setMessages);
                    return $result;
                }
                if ($codeData == 'required') {
                    $response['message'] =__("code is required");
                    $response['code'] = '0';
                    $result->setData($response);
                    $this->setMessages($response, $setMessages);
                    return $result;
                }
            } else {
                $response['message'] =__("The gift code %1 is not valid", $param['code']);
                $response['code'] = '0';
                $result->setData($response);
                $this->setMessages($response, $setMessages);
                return $result;
            }
        } else {
            $collection = $this->_salesRule->getCollection()->load();
            foreach ($collection as $mo) {
                // Delete coupon
                if ($mo->getName() == $param['code']) {
                    $mo->delete();
                    $this->_backendSession->setCoupancode(null);
                    $this->_backendSession->setReduceprice(null);
                }
            }
            $response['message'] =__("Please enter a valid amount");
            $response['code'] = '0';
            $result->setData($response);
            $this->setMessages($response, $setMessages);
            return $result;
        }
    }

    public function setMessages($response, $flag)
    {
        if ($flag) {
            if ($response['code']) {
                $this->messageManager->addSuccess($response['message']);
            } else {
                $this->messageManager->addError($response['message']);
            }
        }
    }

    public function userCheck($collectionData, $customerEmail)
    {
        if ($collectionData[0]['email'] != $customerEmail) {
            $data = 'error';
            return $data;
        }
    }

    public function codeCheck($isExpire, $model, $param)
    {
        if ($isExpire) {
            foreach ($model as $giftUserModel) {
                $giftUserModel->setIsExpire(1);
                $giftUserModel->save();
            }
            return 'apply';
           
        }
        if ($model->getColumnValues('is_active')[0] != "yes") {
            return 'disable';
           
        }
    }
 
    public function codeApply($customerEmail, $param, $collections)
    {
        $usermodel=$collections->addFieldToFilter("email", $customerEmail)->addFieldToFilter(
            "code",
            $param['code']
        );
        $acamm=0;
        if ($usermodel->getSize() > 0) {
            foreach ($usermodel as $u) {
                $acamm=(real)$u->getRemainingAmt();
            }
            if ((real)$param['amount']>$acamm) {
                $param['amount']=$acamm;
            }
        }
        if ((real)$param['amount']==0) {
            $collection = $this->_salesRule->getCollection();
            foreach ($collection as $mo) {
                if ($mo->getName() == $param['code']) {
                    $mo->delete();
                    $this->_backendSession->setCoupancode(null);
                    $this->_backendSession->setReduceprice(null);
                }
            }
            return 'expire';
          
        } elseif ((real)$param['amount']<=$acamm) {
            $saveQuote =  $this->codeSaveInQuote($param, $collections);
            return $saveQuote;
        } else {
            $this->_backendSession->setCoupancode(null);
            $this->_backendSession->setReduceprice(null);
            return 'valid';
           
        }
    }
    public function codeSaveInQuote($param, $collections)
    {
        if (!empty($param['code'])) {
            $model=$collections->addFieldToFilter("code", $param['code']);
            foreach ($model as $m) {
                $giftcode=$m->getCode();
            }
            if ($giftcode==$param['code']) {
                $this->_checkoutSession->setGift(true);
                $rates = $this->_dataHelper->getCurrentCurrencyRate();
                $dis = (real)$param['amount']*$rates;
                $quoteDiscount= $this->_quote->getQuote()->getSubtotalWithDiscount();
                $quoteSubtotal= $this->_quote->getQuote()->getSubtotal();
                if ($quoteDiscount >= $dis) {
                    $discount = $dis;
                } else {
                    $discount = $quoteDiscount;
                }
                 $this->_quote->getQuote()->setShippingAmount(90)->save();
                    $this->_checkoutSession->setAmount(-$discount);
                    $this->_checkoutSession->setGiftCode($param['code']);
                    $this->_quote
                    ->getQuote()
                    ->collectTotals()
                    ->save();
                    return 'success';
            } else {
                return 'valid';
            }
        } else {
            return 'required';
           
        }
    }
}
