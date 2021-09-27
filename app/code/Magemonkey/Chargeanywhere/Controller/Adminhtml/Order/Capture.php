<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magemonkey\Chargeanywhere\Controller\Adminhtml\Order;
use Magento\Backend\App\Action;

class Capture extends \Magento\Sales\Controller\Adminhtml\Order
{
  public function execute()
  {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $helper = $objectManager->create('\Magemonkey\Chargeanywhere\Helper\Data');
    $logger = $objectManager->get('\Magemonkey\Chargeanywhere\Logger\Logger');
    $invoiceService = $objectManager->create('\Magento\Sales\Model\Service\InvoiceService');
    $transaction = $objectManager->create('\Magento\Framework\DB\Transaction');
    $invoiceSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
    $order = $this->_initOrder();

    if ($order->canInvoice()) {
      $orderid =  $order->getId();
      $paymentid = $order->getPayment()->getId();
      $transcollection = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection')
        ->addOrderIdFilter($orderid)
        ->addPaymentIdFilter($paymentid)
        ->getFirstItem();
      
      $transactionid = $transcollection->getTxnId();

      $helper = $objectManager->create('\Magemonkey\Chargeanywhere\Helper\Data');
      $logger = $objectManager->get('\Magemonkey\Chargeanywhere\Logger\Logger');

      $marchantid = $helper->getConfig('marchant_id');
      $terminalid = $helper->getConfig('terminal_id');
      
      $mode = 2;
      $amount = $order->getGrandTotal();
      $OriginalReferenceNumber = $transactionid;
      $transactiontype = 'Force';
      $seed = strtotime("now");
      $secret = $helper->getConfig('secret_key');
      $incrementid = $order->getIncrementId();
    
      $fieldval = "MerchantId=".$marchantid."&TerminalId=".$terminalid."&Secret=".$secret."&Version=1.3&Amount=".$amount."&InvoiceNumber=".$incrementid."&TransactionType=".$transactiontype."&Mode=".$mode."&OriginalReferenceNumber=".$OriginalReferenceNumber;
      
      $url = $helper->getModeType();
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt ($curl, CURLOPT_POST, 1);
      curl_setopt ($curl, CURLOPT_POSTFIELDS, $fieldval);
      curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: text/html', 'Content-Type: application/x-www-form-urlencoded','Cache-Control: no-cache','Pragma: no-cache'));  
      $response = curl_exec($curl); 
        
      curl_close ($curl);

      $redata = explode("&", $response);
      $flag = 0;
      $rssdata = array();
      foreach ($redata as $key => $reval) {
          $revaldata = explode("=", $reval);
          if($revaldata[0] == 'ResponseText' && $revaldata[1] == 'APPROVED'){
              $flag = 1;
              $rssdata['ResponseText'] = $revaldata[1];
          }
          if($flag == 1){
            if($revaldata[0] == 'ReferenceNumber'){
                  $rssdata['ReferenceNumber'] = $revaldata[1];
              }
              if($revaldata[0] == 'ProcessorReferenceNumber'){
                 $rssdata['ProcessorReferenceNumber'] = $revaldata[1];
              }
              if($revaldata[0] == 'ApprovalCode'){
                 $rssdata['ApprovalCode'] = $revaldata[1];
              }
          }
            
      }
      if($flag == 0){
          $logger->info(print_r($response,true));
          $message = __('Amount not captured. Please check log and try to capture again.');
          $this->messageManager->addError($message);
          $logger->info(print_r('*************** Capture Error '.$OriginalReferenceNumber.'***************',true));
          $logger->info(print_r($response,true));
          $logger->info(print_r('*************** Capture Error '.$OriginalReferenceNumber.'***************',true));
          $resultRedirect = $this->resultRedirectFactory->create();
          $resultRedirect->setPath('sales/*/view', ['order_id' => $order->getId()]);
          return $resultRedirect;

      }
      $logger->info(print_r('*************** Capture Response '.$OriginalReferenceNumber.'***************',true));
      $logger->info(print_r($response,true));
      $logger->info(print_r('*************** Capture Response '.$OriginalReferenceNumber.'***************',true));


      $invoice = $invoiceService->prepareInvoice($order);
      $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
      $invoice->register();            
      $invoice->getOrder()->setCustomerNoteNotify(false);
      $invoice->getOrder()->setIsInProcess(true);
      $invoice->save();
      $transactionSave = $transaction->addObject(
          $invoice
      )->addObject(
          $invoice->getOrder()
      );
      $transactionSave->save();
      $invoiceSender->send($invoice);

      $order->addStatusHistoryComment(
          __('Invoice created #%1.', $invoice->getId())
      )
          ->setIsCustomerNotified(false)
          ->save();
    }
    /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
    $resultRedirect = $this->resultRedirectFactory->create();
    $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
    return $resultRedirect;
  }
}
