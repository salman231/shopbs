<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Observer\MagemonkeyChargeanywhere;

use Magento\Framework\Event\Observer;

class BeforePlaceOrder extends \Magemonkey\Chargeanywhere\Observer\BeforePlaceOrder
{
    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlInterface = $objectManager->create(\Magento\Framework\UrlInterface::class);

        $url = $urlInterface->getCurrentUrl();
        
        if (stripos($url, "mobikulhttp/checkout/PlaceOrder") == true) {
            $paymentOrder = $observer->getEvent()->getOrder()->getPayment();
            $paymentData = $paymentOrder->getData();
            $paymentInfo["paymentMethod"]["method"] = $paymentData["method"];
            $paymentInfo["paymentMethod"]['additional_data'] = [];
            if ($paymentData["cc_cid"] != "") {
                $paymentInfo["paymentMethod"]['additional_data']["cc_cid"] = $paymentData["cc_cid"];
            }
            if ($paymentData["cc_exp_month"] != "") {
                $paymentInfo["paymentMethod"]['additional_data']["cc_exp_month"] = $paymentData["cc_exp_month"];
            }
            if ($paymentData["cc_exp_year"] != "") {
                $paymentInfo["paymentMethod"]['additional_data']["cc_exp_year"] = $paymentData["cc_exp_year"];
            }
            if ($paymentData["cc_number"] != "") {
                $paymentInfo["paymentMethod"]['additional_data']["cc_number"] = $paymentData["cc_number"];
            }
            if ($paymentData["cc_type"] != "") {
                $paymentInfo["paymentMethod"]['additional_data']["cc_type"] = $paymentData["cc_type"];
            }
            // $this->request->setParams($paymentInfo);
            $paymentOrder = $observer->getEvent()->getPayment();
            $quote = $this->_checkoutSession->getQuote();
            $reservedOrderId = $quote->getReservedOrderId();

            $inputData = $paymentInfo;
            // $this->logger->info(print_r($inputData['paymentMethod'],true));
            $this->logger->info(print_r($quote->getId(),true));
            $quoteitems = $quote->getAllItems();
            $productype = '';
            $duration = '';
            $durationUnit = '';
            $redirectUrl = $this->_cartHelper->getCartUrl();
            $secret = $this->helper->getConfig('secret_key');
            $marchntid = $this->helper->getConfig('marchant_id');
            $trmnlid = $this->helper->getConfig('terminal_id');
            if($secret == '' || $marchntid == '' || $trmnlid == ''){
                $message = __('Please check payment configuration');
                $this->messageManager->addError($message);
                $redirectUrl = $this->_cartHelper->getCartUrl();
                // return $observer->getControllerAction()->getResponse()->setRedirect($redirectUrl);
                return false;
            }

            $protype = array('giftcard', 'Membership','downloadable','virtual');
            $recurringfnd = 0;
            foreach ($quoteitems as $key => $item) {
                
                $ptype = $item->getProductType();
                $this->logger->info(print_r($item->getProductType(),true));
                $this->logger->info(print_r($item->getParentId(),true));
                if(in_array($ptype, $protype)){
                    $isrecurring = $item->getProduct()->getIsRecurring();
                    if($isrecurring == 1){
                        $recurringfnd = 1;
                    }
                    if(!$item->getParentItemId() && $ptype == 'virtual'){
                        $productype = $ptype;
                    }else if($ptype == 'Membership'){
                        $productype = $ptype;
                        //$product_options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                        //$customer_plan = unserialize($product_options['info_buyRequest']['duration_option']);

                        $customOptions = $item->getOptionByCode('additional_options');
                        $row = $customOptions->getData();
                        $newrow=[];
                        $newrow=$row['value'];
                        $roww=json_decode($newrow,TRUE);
                        $durationlabel=$roww['duration']['label'];
                        $durationvalue=$roww['duration']['value'];
                        $durationunitlabel=$roww['duration_unit']['label'];
                        $durationunitvalue=$roww['duration_unit']['value'];
                        //echo $durationlabel." = ".$durationvalue.", ";
                        //echo $durationunitlabel." = ".$durationunitvalue;

                        //$duration = $customer_plan['duration'];
                        $duration = $durationvalue;
                        $customer_plan['duration_unit']= $durationunitvalue; 
                        if($customer_plan['duration_unit'] == "Day"){
                        $durationUnit = 0;  
                        }else if($customer_plan['duration_unit'] == "Week"){
                        $durationUnit = 1;                          
                        }else if($customer_plan['duration_unit'] == "Month"){
                        $durationUnit = 3;                          
                        }else{
                        $durationUnit = 4;                          
                        }
                        break;
                    }else if($ptype == 'giftcard' || $ptype == 'downloadable'){
                        $productype = $ptype;
                    }else{
                        $productype = '';
                    }
                }
            }
            // exit;
            if($inputData['paymentMethod']['method'] == 'chargeanywhere'){
                foreach ($inputData['paymentMethod']['additional_data'] as $key => $ccdata) {
                    if($key == 'cc_number'){
                        $ccnumber = $ccdata;
                    }
                    if($key == 'cc_exp_month'){
                        $ccexmnth = $ccdata;
                    }
                    if($key == 'cc_exp_year'){
                        $ccexyear = $ccdata;
                    }
                    if($key == 'cc_cid'){
                        $cvv = $ccdata;
                    }
                    if($key == 'cc_type'){
                        $cctype = $ccdata;
                    }
                }
                $ccexyear = substr( $ccexyear, -2);
                
                $marchantid = $this->helper->getConfig('marchant_id');
                $terminalid = $this->helper->getConfig('terminal_id');
                $orderid = $reservedOrderId;
                $mode = 2;
                $amount = $this->_checkoutSession->getQuote()->getGrandTotal();
                $authtype = $this->helper->getConfig('payment_action');
                if($authtype == 'authorize' && $productype == ''){
                    $transactiontype = 'Auth%20Only';
                }else{
                    $transactiontype = 'Sale';
                }
                if($productype == 'Membership' || $recurringfnd == 1){
                    $recurring = "&IsRecurring=1&RecurringEffectiveDate=".date("m-d-y")."&RecurringFrequency=".$durationUnit."&RecurringPayments=".$duration;
                }else{
                    $recurring = '';
                }
                $directresponse = 1;
                $seed = strtotime("now");
                
                // $secret = '2050165252205016525220501652522050165251';
                $url = $this->helper->getModeType();
                $cardnumber = $ccnumber;
                $expyear = $ccexyear;
                $typecc = $cctype;
                $expmonth = $ccexmnth;
                $cvv = $cvv;
                if(strlen($expmonth) > 1){
                    $expmonth = $ccexmnth;
                }else{
                    $expmonth = "0".$ccexmnth;;
                }
                $fieldval = "MerchantId=".$marchantid."&TerminalId=".$terminalid."&Seed=".$seed."&Secret=".$secret."&Version=1.3&Amount=".$amount."&InvoiceNumber=".$orderid."&TransactionType=".$transactiontype."&Mode=".$mode."&DirectResponse=".$directresponse."&CardNumber=".$cardnumber."&ExpMonth=".$expmonth."&ExpYear=".$expyear."&CVV=".$cvv.$recurring;
                $custfieldval = "MerchantId=".$marchantid."&TerminalId=".$terminalid."&Seed=".$seed."&Secret=".$secret."&Version=1.3&Amount=".$amount."&InvoiceNumber=".$orderid."&TransactionType=".$transactiontype."&Mode=".$mode."&DirectResponse=".$directresponse.$recurring;
                $this->logger->info(print_r($custfieldval,true));
                // exit;
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt ($curl, CURLOPT_POST, 1);
                curl_setopt ($curl, CURLOPT_POSTFIELDS, $fieldval);
                curl_setopt($curl, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: text/html', 'Content-Type: application/x-www-form-urlencoded','Cache-Control: no-cache','Pragma: no-cache','Accept-Charset: utf-8'));  
                $response = curl_exec($curl); 

                $redata = explode("&", $response);
                $flag = 0;
                $rssdata = array();
                foreach ($redata as $key => $reval) {
                    $revaldata = explode("=", $reval);
                    if($revaldata[0] == 'ResponseText' && $revaldata[1] == 'APPROVED'){
                        $flag = 1;
                        $rssdata['ResponseText'] = $revaldata[1];
                    }
                    if($flag == 1 && $productype == 'Membership'){
                        if($revaldata[0] == 'RecurringResponse' && $revaldata[1] == 0){
                        $flag = 0;
                        }   
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
                    $this->logger->info(print_r('*************** Order Error Response ***************',true));
                    $this->logger->info(print_r($response,true));
                    $this->logger->info(print_r('*************** Order Error Response ***************',true));

                    $message = __('Transaction has not been submitted to the processor successfully. Try entering the card number again.');
                    $this->messageManager->addError($message);
                    $controller = $observer->getControllerAction();
                    
                    // header('Location: ' . $redirectUrl);
                    // exit;
                    return false;
                }
                $rssdata['cc_number'] = 'xxxx'.substr($cardnumber,-4);
                $rssdata['cc_exp_month'] = $expmonth;
                $rssdata['cc_exp_year'] = $expyear;
                $rssdata['cc_type'] = $typecc;
                $rssdata['product_type'] = $productype;
                
                $this->_checkoutSession->setChargeData($rssdata);
                $this->logger->info(print_r('*************** Order Approve Response ***************',true));
                $this->logger->info(print_r($response,true));
                $this->logger->info(print_r($rssdata,true));
                $this->logger->info(print_r('*************** Order Approve Response ***************',true));

                curl_close ($curl);
            }
            return true;
        }
        return parent::execute($observer);
    }
}