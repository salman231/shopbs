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

namespace Webkul\GiftCard\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;

class AfterInvoiceGeneration implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesOrder;
    
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalpgProduct;
    
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_magentoSalesRule;
    
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_magentoSalesOrderItem;
    
    /**
     * @var \Webkul\GiftCard\Helper\Data
     */
    protected $_helperData;
    
    /**
     * @var \Webkul\GiftCard\Model\GiftDetailFactory
     */
    protected $_giftDetailFactory;
    
    /**
     * @var \Webkul\GiftCard\Model\GiftUserFactory
     */
    protected $_giftUserFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     *
     * @param \Magento\Sales\Model\Order                           $salesOrder
     * @param \Magento\Catalog\Model\Product                       $catalpgProduct
     * @param \Magento\SalesRule\Model\Rule                        $magentoSalesRule
     * @param \Magento\Sales\Model\Order\ItemFactory               $magentoSalesOrderItem
     * @param \Webkul\GiftCard\Helper\Data                         $helperData
     * @param \Webkul\GiftCard\Model\GiftDetailFactory             $giftDetailFactory
     * @param \Webkul\GiftCard\Model\GiftUserFactory               $giftUserFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Magento\Framework\Message\ManagerInterface          $messageManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\OrderFactory $salesOrder,
        \Magento\Catalog\Model\ProductFactory $catalpgProduct,
        \Magento\SalesRule\Model\RuleFactory $magentoSalesRule,
        \Magento\Sales\Model\Order\ItemFactory $magentoSalesOrderItem,
        \Webkul\GiftCard\Helper\Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Webkul\GiftCard\Model\GiftDetailFactory $giftDetailFactory,
        \Webkul\GiftCard\Model\GiftUserFactory $giftUserFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderRepository $orderRepo,
        \Magento\Directory\Model\CurrencyFactory $currency,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->coreSession = $coreSession;
        $this->currency = $currency;
        $this->orderRepo = $orderRepo;
        $this->store = $store;
        $this->logger = $logger;
        $this->_salesOrder = $salesOrder;
        $this->_catalpgProduct = $catalpgProduct;
        $this->_magentoSalesRule = $magentoSalesRule;
        $this->_magentoSalesOrderItem = $magentoSalesOrderItem;
        $this->_helperData = $helperData;
        $this->_giftDetailFactory = $giftDetailFactory;
        $this->_giftUserFactory = $giftUserFactory;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_messageManager = $messageManager;
    }

    public function getCustomerData($customOptions)
    {
        $data['userEmail'] = "";
        $data['userMessage'] = "";
        if (!empty($customOptions)) {
            foreach ($customOptions as $option) {
                if ($option['label'] == 'Email To') {
                    $data['userEmail'] = $option['value'];
                }
                if ($option['label'] == 'Message') {
                    $data['userMessage'] = strip_tags(htmlspecialchars_decode($option['value']));
                }
            }
        }
        return $data;
    }

    public function saveCode($sl, $_Symbol, $oids, $websiteId)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customfile.log');

        foreach ($sl->getAllItems() as $item) {
            $productid = $item->getProductId();
            $gcqty= (int)$item->getQtyOrdered();
            for ($i=0; $i < $gcqty; $i++) {
                $giftmodel  = $this->_catalpgProduct->create()->load($productid);
                if ($giftmodel->getTypeId() == 'giftcard') {
                    $options = $item->getProductOptions();
                    $customOptions = $options['options'];
                    $cusData = $this->getCustomerData($customOptions);
                    $userEmail = $cusData['userEmail'];
                    $userMessage = $cusData['userMessage'];
                    $customer=$sl->getCustomerEmail();
                    $customer_name=$sl->getCustomerFirstname()." ".$sl->getCustomerLastname();
                    $mailData=[];
                    /* Assign values for your template variables  */
                    $emailTemplateVariables = [];
                    $price= $item->getBasePrice();
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter($writer);
                    $logger->info('==============Price');
                    $logger->info($price);
                    $logger->info(json_encode($item->getData()));
                    $mailData['price']=$price;
                    $emailTemplateVariables['myvar1'] = $price;

                    $emailTemplateVariables['myvar8'] = $_Symbol->getCurrencySymbol();
                    $des = strip_tags(htmlspecialchars_decode($giftmodel->getDescription()));
                    $mailData['description']=$des;
                    $emailTemplateVariables['myvar2'] = $des;
                    $email = $userEmail;
                    $mailData['reciever']=$email;
                 
                    /* Receiver Detail  */
                    $receiverInfo = [
                        'name' => 'Reciver Name',
                        'email' => $email
                    ];
                    $emailTemplateVariables['myvar6'] = 'Reciver Name';
                    $emailTemplateVariables['myvar7'] = $email;
                    $emailTemplateVariables['myvar9'] = $userMessage;

                    $giftcode=$this->_helperData->getRandId(12);
                    $mailData['sender']=$customer;
                    $mailData['sender_name']=$customer_name;
                    $emailTemplateVariables['myvar4'] = $customer;
                    $emailTemplateVariables['myvar5'] = $customer_name;
                    
                    /* Sender Detail  */
                    // $senderInfo = [
                    //     'name' => $customer_name,
                    //     'email' => $customer
                    // ];

                    $adminName = $this->_helperData->getAdminNameFromConfig();
                    $adminEmail = $this->_helperData->getAdminEmailFromConfig();
                    if (!isset($adminName) || $adminName == "") {
                        $adminName = $this->_helperData->getStorename();
                    }
                    if (!isset($adminEmail) || $adminEmail == "") {
                        $adminEmail = $this->_helperData->getStoreEmail();
                    }
                    $senderInfo = [
                        'name' => $adminName,
                        'email' => $adminEmail
                    ];

                    
                    // $senderInfo = [
                    //     'name' => $customer_name,
                    //     'email' => 'sales@shop.bs'
                    // ];

                    $usageDurationOfGiftCard = $this->_helperData->getGiftCardActiveDuration();
                    $websiteIds = implode(',', $giftmodel->getWebsiteIds());
                    $this->giftCodeAllote(
                        $email,
                        $price,
                        $des,
                        $customer,
                        $userMessage,
                        $usageDurationOfGiftCard,
                        $oids,
                        $websiteId,
                        $giftcode,
                        $senderInfo,
                        $receiverInfo,
                        $emailTemplateVariables,
                        $websiteIds
                    );
                }
            }
        }
    }
    public function giftCodeAllote(
        $email,
        $price,
        $des,
        $customer,
        $userMessage,
        $usageDurationOfGiftCard,
        $oids,
        $websiteId,
        $giftcode,
        $senderInfo,
        $receiverInfo,
        $emailTemplateVariables,
        $websiteIds
    ) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customfile.log');

        if ($email) {
            try {
                $data=["price"=>$price,"description"=>$des,"email"=>$email,"from"=>$customer,
                "message"=>$userMessage,"duration"=>$usageDurationOfGiftCard,
                'order_id'=>$oids,'website_id'=>$websiteId, 'website_ids'=>$websiteIds];
                $model=$this->_giftDetailFactory->create()->setData($data);

                $dateTimeAsTimeZone = $this->_timezoneInterface
                            ->date(new \DateTime(date("Y/m/d h:i:sa")))
                            ->format('Y/m/d H:i:s');
                $emailTemplateVariables['myvar10'] = $this->_helperData->createExpirationDateOfGiftCard(
                    $usageDurationOfGiftCard,
                    $dateTimeAsTimeZone
                );
            
                    $id=$model->save()->getGiftId();
                    $dadsta = ["giftcodeid"=>$id,"amount"=>$price,"alloted"=>$dateTimeAsTimeZone,"email"=>$email,
                    "from"=>$customer,"remaining_amt"=>$price,"is_active"=>"yes","is_expire"=>0];
                    $model2=$this->_giftUserFactory->create()->setData(["giftcodeid"=>$id,"amount"=>$price,
                    "alloted"=>$dateTimeAsTimeZone,"email"=>$email,"from"=>$customer,
                    "remaining_amt"=>$price,"is_active"=>"yes", "is_expire"=>0]);
                    $id2=$model2->save()->getGiftuserid();

                    $this->_giftDetailFactory->create()->load($id)->setGiftCode($id2.$giftcode)->save();
                    $this->_giftUserFactory->create()->load($id2)->setCode($id2.$giftcode)->save();
                    $emailTemplateVariables['myvar3'] = $id2.$giftcode;
                    $mailData['code']=$id2.$giftcode;
                try {

                    $this->_helperData->customMailSendMethod(
                        $emailTemplateVariables,
                        $senderInfo,
                        $receiverInfo
                    );
                } catch (\Exception $e) {
                            $this->_messageManager->addError(__($e->getMessage()));

                            return false;
                }
            } catch (\Exception $e) {
                        $this->_messageManager->addError(__($e->getMessage()));

                        return false;
            }
        }
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        if ($this->coreSession->getTempId()==$invoice->getId()) {
            return $this;
        }
        $this->coreSession->setTempId($invoice->getId());
        $oids = $invoice->getOrderId();

        $sl = $this->_salesOrder->create()->load($oids);
        $order = $this->orderRepo->get($oids);
        $quote = $this->quoteFactory->create()->load($sl->getQuoteId());

        $storecode = $order->getstoreId();
        $websiteId = '';
        $stores = $this->store->getStores(true, false);
        foreach ($stores as $store) {
            if ($store->getId() === $storecode) {
                $storeData = $store->getData();
                $websiteId = $storeData['website_id'];
            }
        }

        $_Symbol = $this->currency->create()->load($this->_helperData->getBaseCurrencyCode());
        $couponCode=$quote->getGiftCode();
        $discountAmt=$quote->getBasefee();
        $this->saveCode($sl, $_Symbol, $oids, $websiteId);
        $cc=$this->_giftUserFactory->create()->getCollection()->addFieldToFilter('code', $couponCode);
        if ($cc->getSize() && $couponCode) {

            $gift_user_data=[];
            $customerName=$sl->getCustomerFirstname()." ".$sl->getCustomerLastname();
            $customerEmail=$sl->getCustomerEmail();
            $gift_user_data["orderId"]=$sl->getIncrementId();
            $gift_user_data["reciever_email"]=$customerEmail;
            $gift_user_data["reciever_name"]=$customerName;
            $gift_user_data["reduced_ammount"]=$discountAmt;
            $emailTemplateVariablesForLeftAmt["myvar1"]=$sl->getIncrementId();
            $emailTemplateVariablesForLeftAmt["myvar2"]=$customerEmail;
            $emailTemplateVariablesForLeftAmt["myvar3"]=$customerName;
            $emailTemplateVariablesForLeftAmt["myvar4"]=$discountAmt;
            $emailTemplateVariablesForLeftAmt['myvar8'] = $_Symbol->getCurrencySymbol();
            $model3=$this->_giftUserFactory->create()
                        ->getCollection()
                        ->addFieldToFilter("code", $couponCode);
            foreach ($model3 as $m3) {
                $gift_user_data["previous_ammount"]=$m3->getRemainingAmt() - $discountAmt;
                $gift_user_data["gift_code"]=$m3->getCode();
                $emailTemplateVariablesForLeftAmt["myvar5"]=$m3->getRemainingAmt() - $discountAmt;
                $emailTemplateVariablesForLeftAmt["myvar6"]=$m3->getCode();
                $m3->setAmount($m3->getAmount()+$discountAmt)->save();
                $gift_user_data["result_ammount"]=$m3->getAmount();
                $emailTemplateVariablesForLeftAmt["myvar7"]=$m3->getRemainingAmt();
                $giftCodeId = $m3->getGiftcodeid();
                $date = $m3->getAlloted();
            }
            $giftDetailModel = $this->_giftDetailFactory->create()->load($giftCodeId);
            $duration = $giftDetailModel->getDuration();
            $emailTemplateVariablesForLeftAmt["myvar9"] = $date;
            $emailTemplateVariablesForLeftAmt["myvar10"] = $this->_helperData->createExpirationDateOfGiftCard(
                $duration,
                $date
            );
            $receiverInfo = [
                'name' => $customerName,
                'email' => $customerEmail
            ];
            $adminName = $this->_helperData->getAdminNameFromConfig();
            $adminEmail = $this->_helperData->getAdminEmailFromConfig();
            if (!isset($adminName) || $adminName == "") {
                $adminName = $this->_helperData->getStorename();
            }
            if (!isset($adminEmail) || $adminEmail == "") {
                $adminEmail = $this->_helperData->getStoreEmail();
            }
            $senderInfo = [
                'name' => $adminName,
                'email' => $adminEmail
            ];
            $emailTemplateVariablesForLeftAmt['myvar8'] = $this->_helperData->getBaseCurrencyCode();
            $this->_helperData->customMailSendMethodForLeftAmt(
                $emailTemplateVariablesForLeftAmt,
                $senderInfo,
                $receiverInfo
            );
        }
    }
}
