<?php

$path = getcwd();
require_once($path.'/../app/bootstrap.php');

use \Magento\Framework\App\Bootstrap;

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('\Magento\Framework\App\State');
$state->setAreaCode('frontend');

$url="https://shop.bs/index.php/";
$token_url = $url."rest/V1/integration/admin/token";
$cat_api_url = $url. "rest/V1/categories/";



$inputJSON = file_get_contents('php://input');
$dataArr = json_decode($inputJSON, TRUE );


$username = $dataArr['apiUser'];
$password = $dataArr['apiKey'];

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

if($token == ""){ 
	echo '{"status":"NO","message":"Invalid API credencials."}';
}
$adminToken =  json_decode($token);

$cat_tree = getCategoryTreeView($adminToken,$cat_api_url);

$arr = array("status"=>"NO","categories"=>$cat_tree);

echo json_encode($arr);

function getCategoryTreeView($adminToken,$cat_api_url){
	
	$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $cat_api_url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);

	$data = json_decode($response, TRUE);
	
	return $data;
} 

exit;