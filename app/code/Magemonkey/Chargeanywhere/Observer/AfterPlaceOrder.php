<?php
namespace Magemonkey\Chargeanywhere\Observer;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Service\InvoiceService;

class AfterPlaceOrder implements ObserverInterface {
    protected $_inputParamsResolver;
    protected $_quoteRepository;
    protected $logger;
    protected $_state;
    protected $_customerSession;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository, 
        \Magemonkey\Chargeanywhere\Logger\Logger $logger,
        \Magento\Framework\App\State $state,         
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        Transaction $transaction,
        InvoiceService $invoiceService
    ) {
        $this->_quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->_state = $state;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->transaction = $transaction;
        $this->invoiceService = $invoiceService;
    }
    public function execute(EventObserver $observer) {
        $order = $observer->getEvent()->getOrder();
        $chargedata = $this->_checkoutSession->getChargeData();
        /*if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->setIsPaid(true);
            $invoice->register();            
            $invoice->save();
            $transactionSave = $this->transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            // $this->invoiceSender->send($invoice);
  
            $order->addStatusHistoryComment(
                __('Notified customer about invoice creation #%1.', $invoice->getId())
            )
                ->setIsCustomerNotified(false)
                ->save();
        }*/
    }

}