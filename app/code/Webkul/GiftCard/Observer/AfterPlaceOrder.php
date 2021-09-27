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
use Magento\Sales\Api\InvoiceRepositoryInterface;

class AfterPlaceOrder implements ObserverInterface
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesOrder;
    
    /**
     * @var \Webkul\GiftCard\Model\GiftUserFactory
     */
    protected $_giftUserFactory;
    
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_salesRule;

    /**
     * Constructor.
     *
     * @param \Magento\Sales\Model\Order                         $salesOrder
     * @param \Webkul\GiftCard\Model\GiftUserFactory             $giftUserFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\SalesRule\Model\Rule                      $salesRule
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Sales\Model\Order $salesOrder,
        \Webkul\GiftCard\Model\GiftUserFactory $giftUserFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\SalesRule\Model\Rule $salesRule,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->quoteFactory = $quoteFactory;
        $this->_salesOrder = $salesOrder;
        $this->_giftUserFactory = $giftUserFactory;
        $this->_session = $session;
        $this->_customerSession = $customerSession;
        $this->_salesRule = $salesRule;
        $this->logger = $logger;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $oids=$observer->getOrderIds();
        $sl = $this->_salesOrder->load($oids);
        $quote = $this->quoteFactory->create()->load($sl->getQuoteId());
        if (!empty($quote->getGiftCode())) {
            $gift_user_data=[];
            $customerEmail=$this->_customerSession->getCustomer()->getEmail();
            $customerName=$this->_customerSession->getCustomer()->getName();
            $gift_user_data["orderId"]=$sl->getIncrementId();
            $gift_user_data["reciever_email"]=$customerEmail;
            $gift_user_data["reciever_name"]=$customerName;
            $gift_user_data["reduced_ammount"]=$quote->getBasefee();
            $model3=$this->_giftUserFactory->create()
            ->getCollection()
            ->addFieldToFilter("code", $quote->getGiftCode());
            foreach ($model3 as $m3) {
                $amnt=$m3->getRemainingAmt();
                $m3->setRemainingAmt($amnt+$quote->getBasefee())->save();
            }
            $invoiceId = '';
            foreach ($sl->getInvoiceCollection() as $invoice) {
                $invoiceId = $invoice->getId();
            }
            if (!empty($invoiceId)) {
                $sl->setFee($quote->getBasefee());
                $sl->save();
                $invoiceData = $this->invoiceRepository->get($invoiceId);
                $invoiceData->setFee($quote->getBasefee());
                $invoiceData->setGrandTotal($invoiceData->getGrandTotal() + $quote->getFee());
                $invoiceData->setBaseGrandTotal($invoiceData->getBaseGrandTotal() + $quote->getBasefee());
                $invoiceData->save();
            }
            $this->_checkoutSession->setGift(false);
            $this->_checkoutSession->setAmount('');
            $this->_checkoutSession->setGiftCode('');
        }
    }
}
