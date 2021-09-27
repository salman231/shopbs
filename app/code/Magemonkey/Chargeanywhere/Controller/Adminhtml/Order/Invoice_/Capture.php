<?php

namespace Magemonkey\Chargeanywhere\Controller\Adminhtml\Order\Invoice;


class Capture extends \Magento\Sales\Controller\Adminhtml\Order\Invoice\Capture
{

	/**
     * Capture invoice action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
    	$invoice = $this->getInvoice();
        if (!$invoice) {
            /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
        try {
            $invoiceManagement = $this->_objectManager->get(\Magento\Sales\Api\InvoiceManagementInterface::class);
            $invoiceManagement->setCapture($invoice->getEntityId());
            $invoice->getOrder()->setIsInProcess(true);
            $orderid = $invoice->getOrder()->getId();
       		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderid);
       		$paymentid = $order->getPayment()->getId();
       		$transcollection = $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection')
       			->addOrderIdFilter($orderid)
       			->addPaymentIdFilter($paymentid)
       			->getFirstItem();
       		$transactionid = $transcollection->getTxnId();

       		$helper = $this->_objectManager->create('\Magemonkey\Chargeanywhere\Helper\Data');
      		$logger = $this->_objectManager->get('\Magemonkey\Chargeanywhere\Logger\Logger');

      		$marchantid = $helper->getConfig('marchant_id');
	      	$terminalid = $helper->getConfig('terminal_id');
	      	$orderid = $order->getIncrementId();
	      	$mode = 2;
	      	$amount = $order->getGrandTotal();
	      	$OriginalReferenceNumber = $transactionid;
	      	$transactiontype = 'Force';
	      	$seed = strtotime("now");
	      	$secret = $helper->getConfig('secret_key');
	      
	      	$fieldval = "MerchantId=".$marchantid."&TerminalId=".$terminalid."&Secret=".$secret."&Version=1.3&Amount=".$amount."&InvoiceNumber=".$orderid."&TransactionType=".$transactiontype."&Mode=".$mode."&OriginalReferenceNumber=".$OriginalReferenceNumber;


            
            $this->_objectManager->create(
                \Magento\Framework\DB\Transaction::class
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            )->save();
            $this->messageManager->addSuccess(__('The invoice has been captured.'));

            $url = $helper->getModeType();
		    $curl = curl_init();
		    curl_setopt($curl, CURLOPT_URL, $url);
		    curl_setopt ($curl, CURLOPT_POST, 1);
		    curl_setopt ($curl, CURLOPT_POSTFIELDS, $fieldval);
		    curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: text/html', 'Content-Type: application/x-www-form-urlencoded','Cache-Control: no-cache','Pragma: no-cache'));  
		    $response = curl_exec($curl); 
		      // print_r($response);
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
		        $message = __('An error occurred from Charge Anywhere. Please try to place the order again.');
		        $this->messageManager->addError($message);
		        $resultRedirect = $this->resultRedirectFactory->create();
		        $resultRedirect->setPath('sales/*/view', ['invoice_id' => $invoice->getId()]);
		        return $resultRedirect;

		    }
		    $logger->info(print_r('***************response '.$OriginalReferenceNumber.'***************',true));
		    $logger->info(print_r($response,true));
		    $logger->info(print_r('***************response '.$OriginalReferenceNumber.'***************',true));


        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/*/view', ['invoice_id' => $invoice->getId()]);
        return $resultRedirect;
    }

}

	