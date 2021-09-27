<?php
$path = getcwd();
require_once($path.'/../app/bootstrap.php');
use \Magento\Framework\App\Bootstrap;


$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('\Magento\Framework\App\State');
$state->setAreaCode('frontend'); 

error_reporting(0);
$inputJSON = file_get_contents('php://input');
$dataArr = json_decode($inputJSON, TRUE );
$username=$dataArr['apiUser'];
$password = $dataArr['apiKey'];


function getDownloadRecentOrdersList($par)
{
	
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	
			$last_orderId = (int)$par['order_id'];
			//$orderId entity_id
			
			$order = $objectManager->create('Magento\Sales\Model\Order')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('entity_id',array(array('gt'=>$last_orderId)));
			$ordersArr = $order->getData();
			
			if(sizeof($ordersArr) <= 0){
					return array("status" => "NO", "message" => "There are not any new orders.");
			}
			
			foreach($ordersArr as $order){
				
					$orderId = $order['increment_id'];
					$entity_id = $order['entity_id'];
					
					$customer_email = $order['customer_email'];
					$customer_firstname = $order['customer_firstname'];
					$customer_lastname = $order['customer_lastname'];
					$customer_email = $order['customer_email'];
					$customer_id = $order['customer_id'];
					$status = $order['status'];
					
					$grand_total = round($order['grand_total']);
					$base_subtotal_incl_tax = round($order['base_subtotal_incl_tax']);
					$total_qty_ordered  = (int)$order['total_qty_ordered'];
					$tax_amount = round($order['tax_amount']);
					$shipping_amount = round($order['shipping_amount']);
				    
					$base_discount_amount = round($order['base_discount_amount']);
					
					$base_shipping_hidden_tax_amount = isset($base_shipping_hidden_tax_amount)?$base_shipping_hidden_tax_amount: "";
					
					
					$base_shipping_hidden_tax_amount = round($order['base_shipping_hidden_tax_amount']);
					$base_shipping_incl_tax = round($order['base_shipping_incl_tax']);
					
					$base_shipping_amount = round($order['base_shipping_amount']);
					$base_shipping_discount_amount = round($order['base_shipping_discount_amount']);
					$base_discount_amount = round($order['base_discount_amount']);
					$subtotal = round($order['subtotal']);
					$base_subtotal_incl_tax = round($order['base_subtotal_incl_tax']);
					$tax_amount = round($order['tax_amount']);
					$created_at= $order['created_at'];
					$base_tax_amount = round($order['base_tax_amount']);
					$base_grand_total = round($order['base_grand_total']);
					
					$orderObj = $objectManager->create('Magento\Sales\Model\Order')->load($entity_id);
					
					$paymentData_temp = $orderObj->getPayment()->getData();
					
					$paymentData = array();
					
					foreach($paymentData_temp as $key=>$val){
						
						if($key =='row_total' || $key =='base_row_total' || $key =='base_original_price' || $key =='original_price' || $key =='base_price' || $key =='price' || $key == 'shipping_amount' || $key == 'base_shipping_amount' || $key == 'base_amount_ordered' || $key == 'amount_ordered'){
							
							$paymentData[$key] = round($val);
							
						}else{
							$paymentData[$key] = $val;
						}
						
					}

					
					$orderItemsData[] = array(
						'order_id' => $orderId,
						'increment_id' => $orderId,
						'entity_id' => $entity_id,
						'base_discount_amount' =>$base_discount_amount,
						'base_shipping_incl_tax'=>$base_shipping_incl_tax,
						'base_shipping_amount'=>$base_shipping_amount,
						'subtotal'=>$subtotal,
						'tax_amount'=>$tax_amount,
						'base_subtotal_incl_tax'=>$base_subtotal_incl_tax,
						'base_shipping_discount_amount'=>$base_shipping_discount_amount,
						'shipping_amount'=>$shipping_amount,
						'base_shipping_hidden_tax_amount'=>$base_shipping_hidden_tax_amount,
						'shipping_amount'=>$shipping_amount,
						'shipping_amount'=>$shipping_amount,
						'created_at'=>$created_at,
						'base_tax_amount'=>$base_tax_amount,
						'base_grand_total'=>$base_grand_total,
						'status'=>$status,
						'grand_total' => $grand_total,
						'total_qty_ordered' => $total_qty_ordered,
						'tax_amount' => $tax_amount,
						'shipping_amount'=>$shipping_amount,
						'payment_info'=>$paymentData,
						'customer_id'=>$customer_id,
						'customer_firstname' => $customer_firstname,
						'customer_lastname' => $customer_lastname,
						'customer_email' => $customer_email,
					);
					
					
				
			}
					
	return array("status" => "YES", "orderlist" => $orderItemsData);

}
    
$arr = getDownloadRecentOrdersList($dataArr);
echo json_encode($arr);
exit;


?>