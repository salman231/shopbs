<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Helper;

use Magento\Framework\UrlInterface;

/**
 * Custom GiftCard Data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_currency;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_customerSession;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $inlineTranslation;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_transportBuilder;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_timezoneInterface;

    const XML_PATH_EMAIL_TEMPLATE_FIELD  = 'giftcard/email/gift_notification_mail';

    const XML_PATH_GIFT_LEFT_AMT_EMAIL_TEMPLATE_FIELD  = 'giftcard/adminEmail/admin_amt_notification_mail';

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Directory\Model\Currency                    $currency
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Framework\Translate\Inline\StateInterface   $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder    $transportBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\SalesRule\Model\Rule $salesRule,
        \Magento\Checkout\Model\Cart $quote,
        \Magento\Quote\Model\QuoteFactory $quoteData,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->quoteData = $quoteData;
        $this->_storeManager = $storeManager;
        $this->_currency = $currency;
        $this->_customerSession = $customerSession;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_salesRule = $salesRule;
        $this->_quote = $quote;
        $this->_checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * Get log in status
     *
     * @return boolean
     */
    public function isCustomerLoggrdIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
    
    public function sessionCouponPrice()
    {
        return $this->_checkoutSession->getAmount();
    }
    public function clearCoupon()
    {
        $collection = $this->_salesRule->getCollection()->load();
        foreach ($collection as $mo) {
            if ($mo->getName() == $this->_customerSession->getCouponCode()) {
                $mo->delete();
                $this->_customerSession->setAmount(null);
                $this->_customerSession->setCouponCode(null);
                $this->_quote
                ->getQuote()
                ->collectTotals()
                ->save();
                $this->_checkoutSession->setCartWasUpdated(true);
            }
        }
    }
    public function giftAmountManage()
    {
        $quoteData =  $this->_quote->getQuote();
        $giftAmount = explode('-', $quoteData->getFee());
        if (isset($giftAmount[1]) && ($quoteData->getItemsQty() != 0)) {
            if ($quoteData->getSubtotalWithDiscount() >= $giftAmount[1]) {
                $amount = $giftAmount[1];
            } else {
                $amount = $quoteData->getSubtotalWithDiscount();
            }
            $this->_checkoutSession->setAmount(-$amount);
            $this->_checkoutSession->setGift(true);
        } else {
            $amount = 0;
            $this->_checkoutSession->setAmount(null);
            $this->_checkoutSession->setGiftCode(null);
        }
        $quoteId = $this->_quote->getQuote()->getId();
        $this->quoteData->create()->load($quoteId)->setFee(-$amount)->save();
    }

    public function clearGiftCode()
    {
        $quoteId = $this->_quote->getQuote()->getId();
        $this->quoteData->create()->load($quoteId)->setFee(null)->setGiftCode(null)->save();
        $this->_checkoutSession->setAmount(null);
        $this->_checkoutSession->setGiftCode(null);
    }
  
    public function getCouponCode()
    {
        return $this->_checkoutSession->getCouponCode();
    }
    
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }
    
    /**
     * Get current store currency code
     *
     * @return String
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
    
    /**
     * Get default store currency code
     *
     * @return String
     */
    public function getDefaultCurrencyCode()
    {
        return $this->_storeManager->getStore()->getDefaultCurrencyCode();
    }
    
    /**
     * Get allowed store currency codes
     *
     * If base currency is not allowed in current website config scope,
     * then it can be disabled with $skipBaseNotAllowed
     *
     * @param bool $skipBaseNotAllowed
     * @return array
     */
    public function getAvailableCurrencyCodes($skipBaseNotAllowed = false)
    {
        return $this->_storeManager->getStore()->getAvailableCurrencyCodes($skipBaseNotAllowed);
    }
    
    /**
     * Get array of installed currencies for the scope
     *
     * @return array
     */
    public function getAllowedCurrencies()
    {
        return $this->_storeManager->getStore()->getAllowedCurrencies();
    }
    
    /**
     * Get current currency rate
     *
     * @return float
     */
    public function getCurrentCurrencyRate()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyRate();
    }
    
    /**
     * Get currency symbol for current locale and currency code
     *
     * @return String
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     * Get random id for gift code
     *
     * @param int $length
     * @return String
     */
    public function getRandId($length)
    {
        if ($length>0) {
            $rand_id="";
            for ($i=1; $i<=$length; $i++) {
                    $num = random_int(1, 36);
                    $rand_id .= $this->assignRandValue($num);
            }
        }
        return $rand_id;
    }

    /**
     * Assign random values based on random id.
     *
     * @param int $num
     * @return String
     */
    public function assignRandValue($num)
    {
        switch ($num) {
            case "1":
                $rand_value = "A";
                break;
            case "2":
                $rand_value = "B";
                break;
            case "3":
                $rand_value = "C";
                break;
            case "4":
                $rand_value = "D";
                break;
            case "5":
                $rand_value = "E";
                break;
            case "6":
                $rand_value = "F";
                break;
            case "7":
                $rand_value = "G";
                break;
            case "8":
                $rand_value = "H";
                break;
            case "9":
                $rand_value = "I";
                break;
            case "10":
                $rand_value = "J";
                break;
            case "11":
                $rand_value = "K";
                break;
            case "12":
                $rand_value = "L";
                break;
            case "13":
                $rand_value = "M";
                break;
            case "14":
                $rand_value = "N";
                break;
            case "15":
                $rand_value = "O";
                break;
            case "16":
                $rand_value = "P";
                break;
            case "17":
                $rand_value = "Q";
                break;
            case "18":
                $rand_value = "R";
                break;
            case "19":
                $rand_value = "S";
                break;
            case "20":
                $rand_value = "T";
                break;
            case "21":
                $rand_value = "U";
                break;
            case "22":
                $rand_value = "V";
                break;
            case "23":
                $rand_value = "W";
                break;
            case "24":
                $rand_value = "X";
                break;
            case "25":
                $rand_value = "Y";
                break;
            case "26":
                $rand_value = "Z";
                break;
            case "27":
                $rand_value = "0";
                break;
            case "28":
                $rand_value = "1";
                break;
            case "29":
                $rand_value = "2";
                break;
            case "30":
                $rand_value = "3";
                break;
            case "31":
                $rand_value = "4";
                break;
            case "32":
                $rand_value = "5";
                break;
            case "33":
                $rand_value = "6";
                break;
            case "34":
                $rand_value = "7";
                break;
            case "35":
                $rand_value = "8";
                break;
            case "36":
                $rand_value = "9";
                break;
        }
        return $rand_value;
    }

    /**
     * Get the admin name from configuration.
     *
     * @return String
     */
    public function getAdminNameFromConfig()
    {
        return $this->scopeConfig
                    ->getValue(
                        'giftcard/adminEmail/gift_admin_mail_name',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
    }

    /**
     * Get the admin email from the configuration.
     *
     * @return String
     */
    public function getAdminEmailFromConfig()
    {
        return $this->scopeConfig
                    ->getValue(
                        'giftcard/adminEmail/gift_admin_mail_email',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
    }

    /**
     * Get any configuration value depend on the $path.
     *
     * @param String $path
     * @param int $storeId
     * @return depend upon the $path
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Get Email template Id from cofiguration.
     *
     * @return String
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->_storeManager->getStore()->getStoreId());
    }
 
    /**
     * [generateTemplate description]  with template file and tempaltes variables values
     *
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {

        $template =  $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId()
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }
 
    /**
     * [customMailSendMethod Send mail method of Gift Card from Admin to Receiver]
     *
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function customMailSendMethod($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_TEMPLATE_FIELD);

        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();
    }
 
     /**
      * [customMailSendMethodForLeftAmt Send mail of remaining amount from Admin to  Receiver method]
      *
      * @param  Mixed $emailTemplateVariables
      * @param  Mixed $senderInfo
      * @param  Mixed $receiverInfo
      * @return void
      */
    public function customMailSendMethodForLeftAmt($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_GIFT_LEFT_AMT_EMAIL_TEMPLATE_FIELD);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * Checks the expiration of gift card.
     *
     * @param  Date $allotedDate
     * @param  int  $duration
     * @return Boolean
     */
    public function checkExpirationOfGiftCard($allotedDate, $duration, $websiteId = false)
    {
        $expiryDate = strtotime('+'.$duration.' days', strtotime($allotedDate));
        
        $dateTimeAsTimeZone = $this->_timezoneInterface
            ->date(new \DateTime(date("Y/m/d h:i:sa")))
            ->format('Y/m/d H:i:s');

        if ($websiteId !== false) {
            $timeZone = $this->scopeConfig->getValue(
                'general/locale/timezone',
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
            $date = new \DateTime("now", new \DateTimeZone($timeZone));
            $dateTimeAsTimeZone = $date->format('Y-m-d H:i:s');
        }
        
        $dateTimeAsTimeZone = strtotime($dateTimeAsTimeZone);

        return $dateTimeAsTimeZone>=$expiryDate;
    }

    /**
     * Calculate the expiration date of gift card.
     *
     * @param  int  $days
     * @param  Date $date
     * @return Date
     */
    public function createExpirationDateOfGiftCard($days, $date)
    {
            $date = strtotime("+".$days." days", strtotime($date));
            return  date("Y-m-d h:i:s", $date);
    }

    /**
     * Get the active duration of gift card from config.
     *
     * @return int
     */
    public function getGiftCardActiveDuration()
    {
        return $this->getConfigValue(
            "giftcard/activeDuration/gift_admin_active_days",
            $this->_storeManager->getStore()->getStoreId()
        );
    }

    /**
     * Get the store owner name.
     *
     * @return String
     */
    public function getStorename()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_sales/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the store owner email.
     *
     * @return String
     */
    public function getStoreEmail()
    {
        return $this->_scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
