<?php
error_reporting(0);
$path = getcwd();
require_once($path.'/../app/bootstrap.php');
use \Magento\Framework\App\Bootstrap;

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('\Magento\Framework\App\State');
$state->setAreaCode('frontend'); 

$inputJSON = file_get_contents('php://input');
$dataArr = json_decode($inputJSON, TRUE );
$username=$dataArr['apiUser'];
$password = $dataArr['apiKey'];

function getDownloadRecentOrdersList($par)
{
	
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	
			$orderOrgId = (int)$par['order_id'];
			//$orderId entity_id
			$order = $objectManager->create('Magento\Sales\Model\Order')->load($orderOrgId);
			//$orders = $order->getData();
			$orderId = $order['increment_id'];
			$entity_id = $order['entity_id'];
			
			if( $orderId <= 0 || $orderId == ""){
					return array("status" => "NO", "message" => "There are no order avaialble within $orderOrgId order ID.");
			}
			
					
					
					$customer_email = $order['customer_email'];
					$customer_firstname = $order['customer_firstname'];
					$customer_lastname = $order['customer_lastname'];
					$customer_email = $order['customer_email'];
					$customer_id = $order['customer_id'];
					$status = $order['status'];
					
					$grand_total = $order['grand_total'];
					$base_subtotal_incl_tax = $order['base_subtotal_incl_tax'];
					$total_qty_ordered  = (int)$order['total_qty_ordered'];
					$tax_amount = $order['tax_amount'];
					$shipping_amount = $order['shipping_amount'];
					$quote_id  =  $order['quote_id'];
					
					$customer_email = $order['customer_email'];
					$customer_firstname = $order['customer_firstname'];
					$customer_lastname = $order['customer_lastname'];
					$customer_email = $order['customer_email'];
					$customer_id = $order['customer_id'];
					$status = $order['status'];
					
					$grand_total = $order['grand_total'];
					$base_subtotal_incl_tax = $order['base_subtotal_incl_tax'];
					$total_qty_ordered  = (int)$order['total_qty_ordered'];
					$tax_amount = $order['tax_amount'];
					$shipping_amount = $order['shipping_amount'];
				    
					$base_discount_amount = $order['base_discount_amount'];
					$base_shipping_hidden_tax_amount = $order['base_shipping_hidden_tax_amount'];
					$base_shipping_incl_tax = $order['base_shipping_incl_tax'];
					
					$base_shipping_amount = $order['base_shipping_amount'];
					$base_shipping_discount_amount = $order['base_shipping_discount_amount'];
					$base_discount_amount = $order['base_discount_amount'];
					$subtotal = $order['subtotal'];
					$base_subtotal_incl_tax = $order['base_subtotal_incl_tax'];
					$tax_amount = $order['tax_amount'];
					$created_at= $order['created_at'];
					$base_tax_amount = $order['base_tax_amount'];
					$base_grand_total = $order['base_grand_total'];
					
					$order_items = array();
					
					$shipping_address_id =  $order['shipping_address_id'];
					
					$orderObj = $objectManager->create('Magento\Sales\Model\Order')->load($entity_id);
					$paymentData = $orderObj->getPayment()->getData();
					
					$shipping_address = $orderObj->getShippingAddress();
					$customerPrefix = $shipping_address->getPrefix();
					$customerFirstName = $shipping_address->getFirstName(); 
					$customerMiddlename = $shipping_address->getMiddleName();
					$customerLastName = $shipping_address->getLastName(); 
					$customerSuffix = $shipping_address->getSuffix();
					
					$customerCity = $shipping_address->getCity(); 
					$customerTelephone = $shipping_address->getTelephone();
					$customerPostcode = $shipping_address->getPostcode();
					$customerStreet = $shipping_address->getStreet();
					$customerRegionId = $shipping_address->getRegionId();
					$customerStatecode = $shipping_address->getStatecode();
					$customerCountryId = $shipping_address->getCountryId();
					$customerCompany = $shipping_address->getCompany();
					$customerRegion = $shipping_address->getRegion();
					$customerFax = $shipping_address->getFax();
					$customervat = $shipping_address->getVatId();
					
					
					
					if(sizeof($customerStreet) > 0){ $customerStreet = $customerStreet[0];}
					
					$shipping_array = array('shipping_address_id'=>$shipping_address_id,'prefix'=>$customerPrefix,'first_name'=>$customerFirstName,'middle_name'=>$customerMiddlename,'last_name'=>$customerLastName,'suffix'=>$customerSuffix,'street'=>$customerStreet,'city'=>$customerCity,'telephone'=>$customerTelephone,'post_code'=>$customerPostcode,
					'region_id'=>$customerRegionId,'state_code'=>$customerStatecode,'country_id'=>$customerCountryId,'company'=>$customerCompany,'region'=>$customerRegion,
					'fax'=>$customerFax,'vat'=>$customervat);
					
					$billing_address_id =  $order['billing_address_id'];
					$Billing_address = $orderObj->getShippingAddress();
					
					$customerPrefix = $Billing_address->getPrefix();
					$customerMiddlename = $Billing_address->getMiddleName();
					$customerSuffix = $Billing_address->getSuffix();
					
					$customerFirstName = $Billing_address->getFirstName(); 
					$customerLastName = $Billing_address->getLastName(); 
					$customerCity = $Billing_address->getCity(); 
					$customerTelephone = $Billing_address->getTelephone();
					$customerPostcode = $Billing_address->getPostcode();
					$customerStreet = $Billing_address->getStreet();
					$customerRegionId = $Billing_address->getRegionId();
					$customerStatecode = $Billing_address->getStatecode();
					$customerCountryId = $Billing_address->getCountryId();
					$customerCompany = $Billing_address->getCompany();
					$customerRegion = $Billing_address->getRegion();
					$customerFax = $Billing_address->getFax();
					$customervat = $Billing_address->getVatId();
					if(sizeof($customerStreet) > 0){ $customerStreet = $customerStreet[0];}
					
					$Billing_array = array('billing_address_id'=>$billing_address_id,'prefix'=>$customerPrefix,'first_name'=>$customerFirstName,'middle_name'=>$customerMiddlename,'last_name'=>$customerLastName,'suffix'=>$customerSuffix,'street'=>$customerStreet,'city'=>$customerCity,'telephone'=>$customerTelephone,'post_code'=>$customerPostcode,
					'region_id'=>$customerRegionId,'state_code'=>$customerStatecode,'country_id'=>$customerCountryId,'company'=>$customerCompany,'region'=>$customerRegion,
					'fax'=>$customerFax,'vat'=>$customervat);
					
					$order_items = array(); 
					
					$orderItems = $orderObj->getAllItems();
					$paymentData_temp = $orderObj->getPayment()->getData();
					
					$paymentData = array();
					
					foreach($paymentData_temp as $key=>$val){
						
						if($key =='row_total' || $key =='base_row_total' || $key =='base_original_price' || $key =='original_price' || $key =='base_price' || $key =='price' || $key == 'shipping_amount' || $key == 'base_shipping_amount' || $key == 'base_amount_ordered' || $key == 'amount_ordered'){
							
							$paymentData[$key] = round($val);
							
						}else{
							$paymentData[$key] = $val;
						}
						
					}
					
					foreach ($orderItems as $item)
					{
							/*$productId = $item->getProductId();
							$productName =  $item->getName();
							$productSku =  $item->getSku();
							$productOrderedQty =  $item->getQtyOrdered();
							$productWeight =  $item->getWeight();
							$productPrice =  $item->getPrice();
							//$productId = '2115';
							$productObj = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
							$productData = $productObj->getData();
							$order_items[] = array(
								'product_id' => $productId,
								'product_name' => $productName,
								'productSku' => $productSku,
								'product_ordered_qty' => $productOrderedQty,
								'product_weight' => $productWeight,
								'product_price' => $productPrice,
								'product_full_details'=>$productData
							);
							round($price, 2)
							*/
							
							//echo "<pre>";print_r($item->getData());exit;
					
							$iteam_data_temp = $item->getData();
							$item_data = array();
							
							foreach($iteam_data_temp as $key=>$val){
								
								if($key =='row_total' || $key =='base_row_total' || $key =='base_original_price' || $key =='original_price' || $key =='base_price' || $key =='price' ){
									
									$item_data[$key] = round($val);
									
								}else{
									$item_data[$key] = $val;
								}
								
							}
							
							$order_items[] = $item_data;
					} 
					
					$customerInfo = array(
					    'customer_id'=>$customer_id,
						'customer_firstname' => $customer_firstname,
						'customer_lastname' => $customer_lastname,
						'customer_email'=> $customer_email,
						'customer_city' => $customerCity,
						'customer_telephone' => $customerTelephone,
						'customer_postcode' => $customerPostcode,
						'shipping_address' => $customerStreet,
					);
					
					/*$orderInfo = array(
						'order_id' => $orderId,
						'increment_id' => $orderId,
						'entity_id' => $entity_id,
						'base_discount_amount' => $base_discount_amount,
						'base_shipping_incl_tax'=> $base_shipping_incl_tax,
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
						'order_items'=>$order_items,
						'customerInfo'=>$customerInfo,
						'shippingInfo'=>$shipping_array,
						'billingInfo'=>$Billing_array,
					);*/
					return array("status" => "YES", 'order_items'=>$order_items,'customerInfo'=>$customerInfo,'shippingInfo'=>$shipping_array,'billingInfo'=>$Billing_array,'payment_info'=>$paymentData);
					exit;
			

}
    
$arr = getDownloadRecentOrdersList($dataArr);
echo json_encode($arr);
exit;


?>