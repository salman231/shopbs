<?php
error_reporting(0);
$path = getcwd();
require_once($path.'/../app/bootstrap.php');
require_once('ProductCreateFunctions.php');
use \Magento\Framework\App\Bootstrap;

$Baseurl="https://shop.bs/index.php/";

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('\Magento\Framework\App\State');
$state->setAreaCode('frontend'); 

$token_url= $Baseurl."/rest/V1/integration/admin/token";
$product_url = $Baseurl. "/rest/V1/products";


$product_option_url=$Baseurl. "/rest/V1/products/options";


$inputJSON = file_get_contents('php://input');
$dataArr = json_decode($inputJSON, TRUE );
$username = $dataArr['apiUser'];
$password = $dataArr['apiKey'];


$productallData = $dataArr['data'];
 $action = $dataArr['action'];

//Authentication rest API magento2, get access token
$ch = curl_init();
$data = array("username" => $username, "password" => $password);
$data_string = json_encode($data);
$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
);
 $token = curl_exec($ch);
$adminToken =  json_decode($token);
	
if($adminToken == ""){
	$responce = array("status" =>'0', "message"=>'Invalid username or API KEY');
	echo json_encode($responce);exit;
}

$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

$sku = trim($productallData['sku']);
$sku = str_replace(" ","",$sku);	
$productallData['sku'] = $sku; 
//getAttributeIdFromAttrOptions('color', 'red',$Baseurl,$adminToken);exit;
 
if($action == 'create'){
	
	$checkProductExist = checkProductExist($sku);
	if($checkProductExist == 1){ 
		$responce = updateproduct($adminToken,$Baseurl,$productallData);
		$product_option = createproductoption($adminToken,$product_option_url,$productallData);
	
	}else{ 
		$responce = createproduct($adminToken,$product_url,$productallData,$Baseurl);
		$product_option = createproductoption($adminToken,$product_option_url,$productallData);
		
	}
	

}else if($action == 'update'){
	
	$responce = updateproduct($adminToken,$Baseurl,$productallData);
    $product_option = createproductoption($adminToken,$product_option_url,$productallData);
	
}else if($action == 'delete'){
	
	$responce = deleteCatelogProduct($productallData, $adminToken,$Baseurl);
	
}else if($action == 'get'){
	
	$responce = GetProductList($productallData,$adminToken,$Baseurl);
	
}else{
	
	die("Error: wrong request name ".$dataArr['action']);
	
}


echo json_encode($responce);
