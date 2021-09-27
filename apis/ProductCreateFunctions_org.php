<?php
error_reporting(-1);
function getAdminToken($username, $password){
	
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
	
	return $adminToken;
	
}



function updateproduct($adminToken,$Baseurl,$productallData){
	
	$update_array = $productallData['forUpdate'];

	$prdSpecialPrice = isset( $productallData['prdSpecialPrice']) ? $productallData['prdSpecialPrice'] : '';
	$prdSpecialFromDate = isset( $productallData['prdSpecialFromDate']) ? $productallData['prdSpecialFromDate'] : '';
	$prdSpecialToDate = isset( $productallData['prdSpecialToDate']) ? $productallData['prdSpecialToDate'] : '';
	
	$category_ids = $productallData['prdCategories'];
	$description = $productallData['prdDesc'];
	$short_description = $productallData['prdShortDesc'];
	$special_price = $prdSpecialPrice;
	$special_from_date = $prdSpecialFromDate;
	$special_to_date = $prdSpecialToDate;
	$tax_class_id = $productallData['prdTaxId'];
		
	$prd_imge = $productallData['updatePrdImg'];
	$mediaGalleryEntries = array();
	$fist_image_url = "";
	
	
	
	$qty  = isset( $productallData['prdQuantity']) ? $productallData['prdQuantity'] : '0';
	$prdInStock  = isset( $productallData['prdInStock']) ? $productallData['prdInStock'] : '0';
	$prdMngStock  = isset( $productallData['prdMngStock']) ? $productallData['prdMngStock'] : '0';
	$prdMinQty  = isset( $productallData['prdMinQty']) ? $productallData['prdMinQty'] : '0';
	$prdConfigMngStock  = isset( $productallData['prdConfigMngStock']) ? $productallData['prdConfigMngStock'] : '0';
	$prdConfigMinQty  = isset( $productallData['prdConfigMinQty']) ? $productallData['prdConfigMinQty'] : '0';
	$prdMinSaleQty  = isset( $productallData['prdMinSaleQty']) ? $productallData['prdMinSaleQty'] : '0';
	$prdConfigMinSaleQty  = isset( $productallData['prdConfigMinSaleQty']) ? $productallData['prdConfigMinSaleQty'] : '0';
	$prdMaxSaleQty  = isset( $productallData['prdMaxSaleQty']) ? $productallData['prdMaxSaleQty'] : '0';
	$prdConfigMaxSaleQty  = isset( $productallData['prdConfigMaxSaleQty']) ? $productallData['prdConfigMaxSaleQty'] : '0';
	$prdUrlPath  = isset( $productallData['prdUrlPath']) ? $productallData['prdUrlPath'] : '';
	if($prdUrlPath == ""){ $prdUrlPath = str_replace(" ","-",$productallData['prdName']); }
	
	$sku = $productallData['sku'];
	
	$prdUrlPath = str_replace(" ","-",$productallData['prdName']."-".$sku);
	
	$tierPricesFinalArr = array();
	$tierPriceArr = $productallData['prdGroupPrice'];
	
	if(sizeof($tierPriceArr) > 0){
		
			$tirepriceArr = array();
		    foreach($tierPriceArr as $PriceVariations){
				
				$group_id = isset($PriceVariations['groupId']) ?$PriceVariations['groupId'] : '';
				$group_Price = isset( $PriceVariations['price']) ? $PriceVariations['price'] : '';
				$group_qty = isset( $PriceVariations['group_qty']) ? $PriceVariations['group_qty'] : 1;
				
				if($group_id !='' && $group_qty !='' && $group_Price !='')
				{
					$tireprice = array (
							'customer_group_id' => $group_id,
							'qty' => $group_qty,
							'value' => $group_Price,
						);
					
					 $tirepriceArr[] = $tireprice;
				}
	        }	
		$tierPricesFinalArr = $tirepriceArr;
	}
			
	
	$media_url = $Baseurl.'/rest/V1/products/'.$sku.'/media';
	$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
	
	// check media gallery
	
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $media_url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$galleryArr = curl_exec($ch); 
	$galleryArr = json_decode($galleryArr);
	
	if(sizeof($galleryArr) > 0){
		
		foreach($galleryArr as $gallery){
				$entryId = $gallery->id;
				$media_delete_url = $Baseurl.'/rest/V1/products/'.$sku.'/media/'.$entryId;
				$media_data = json_encode(array('sku'=>$sku,'entryId'=>$entryId));
				$ch = curl_init();
				curl_setopt($ch,CURLOPT_URL, $media_delete_url);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $media_data);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				 $response = curl_exec($ch); 
				curl_close($ch);
			
		}
	}
	
	if(count($prd_imge) > 0 && $update_array['prdImage'] == 1){
			
				foreach($prd_imge as $key=>$gallery_img){
						$url = $gallery_img['newImg'];
						if($url != ""){
							
							$content = file_get_contents($url);
							
							if($content != ""){
								 $image_name = $prd_sku.'_'.time().'.jpg';
								 $mediaGalleryEntries[] =  array("id"=>0,"mediaType"=>"image","file"=>$image_name,"label"=>$productallData['prdName'],"position"=> 0,"disabled"=>false,
								  "types"=>array("image", "small_image", "thumbnail"),
								  "content"=>array("base64EncodedData"=>base64_encode($content),"type"=>"image/jpeg","name"=>$image_name));
								  
							}	  
							
						}
				}
	   }
	
	$product_url = $Baseurl."/rest/all/V1/products/".$sku;
	
	UpdateAttributesForCatelogProduct($productallData,$adminToken,$Baseurl);
	
	
	$custom_attributes = array();

	if($update_array['prdUrlPath'] == 1 ){
		$custom_attributes[] = array( 'attribute_code' => 'url_key', 'value' => $prdUrlPath);
	}

	if($productallData['prdCategories'] != "" ){
		$custom_attributes[] = array( 'attribute_code' => 'category_ids', 'value' => $productallData['prdCategories'] );
	}
	
	
	if($productallData['prdDesc'] != "" && $update_array['prdDesc'] == 1){
		$custom_attributes[] = array( 'attribute_code' => 'description', 'value' => $productallData['prdDesc'] );
	}
	
	
	if($productallData['prdShortDesc'] != "" && $update_array['prdShortDesc'] == 1 ){
		$custom_attributes[] = array( 'attribute_code' => 'short_description', 'value' => $productallData['prdShortDesc']);
	}
	
	if($tax_class_id != "" && $update_array['prdTaxId'] == 1){
		$custom_attributes[] = array( 'attribute_code' => 'tax_class_id', 'value' =>$tax_class_id);
	}	
	if($prdSpecialPrice != "" && $prdSpecialPrice > 0 && $update_array['prdSpecialPrice'] == 1){
		$custom_attributes[] = array( 'attribute_code' => 'special_price', 'value' => $prdSpecialPrice);
	}
    if($prdSpecialFromDate != "" && $update_array['prdSpecialFromDate'] == 1){
		$custom_attributes[] = array( 'attribute_code' => 'special_from_date', 'value' => $prdSpecialFromDate);
	}
	if($prdSpecialToDate != ""  && $update_array['prdSpecialToDate'] == 1){
		$custom_attributes[] = array( 'attribute_code' => 'special_to_date', 'value' => $prdSpecialToDate);
	}	
	
	
		$attribute_options_values = array();
		$additionalAttributes = $productallData['additionalAttributes'];
		$isNewAttributeCreated = 'No';
	   
	    if(sizeof($additionalAttributes) > 0){
		
			foreach($additionalAttributes as $addAtts){
					
					foreach($addAtts as $addAtt){
				
										$addAtt = (object)($addAtt); 
										
										$addAttCode = $addAtt->key;
										$AttValue = $addAtt->value;
										
										// GET Attorbute options
										$attribute_option_url = $Baseurl.'/rest/V1/products/attributes/'.$addAttCode.'/options';
										$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
										$ch = curl_init();
										curl_setopt($ch,CURLOPT_URL, $attribute_option_url);
										curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
										curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										$options = curl_exec($ch); 
										curl_close($ch);
										$options_list = json_decode($options, TRUE); 
										
										$isThisOptionAvailable = 'No';
										
										if(sizeof($options_list) > 0){
											foreach($options_list as $optionArr){
												
												 $optionlabel = $optionArr['label'];
												 $optionvalue = $optionArr['value'];
												if(strtolower($optionlabel) == strtolower($AttValue)){
													$custom_attributes[] = array( 'attribute_code' => $addAtt->key, 'value' =>$optionvalue);
													$isThisOptionAvailable = 'Yes';
												}
												
											}
											
										}
										
										// Add new Option for Attribute 
										if($isThisOptionAvailable == 'No'){
											
													$optiondata = array(
													   "label" => (string)$addAtt->value,
													   "sortOrder"=> 0,
													   "isDefault"=>false,
													);
													
													// POST Attribute options
													 $attribute_option = json_encode(array('option' => $optiondata,'attributeCode'=>$addAttCode));
													 $attribute_option_url = $Baseurl.'/rest/V1/products/attributes/'.$addAttCode.'/options';
												
													$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
													$ch = curl_init();
													curl_setopt($ch,CURLOPT_URL, $attribute_option_url);
													curl_setopt($ch,CURLOPT_POSTFIELDS, $attribute_option);
													curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
													curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
													curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
													curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
													$options_id = curl_exec($ch); 
													curl_close($ch);
													$isNewAttributeCreated = 'Yes';
										}
										
					}	
			}
	   }


		 if(sizeof($additionalAttributes) > 0 && $isNewAttributeCreated == 'Yes'){
		
				foreach($additionalAttributes as $addAtts){
						
						foreach($addAtts as $addAtt){
					
											$addAtt = (object)($addAtt); 
											
											$addAttCode = $addAtt->key;
											$AttValue = $addAtt->value;
											
											// GET Attorbute options
											$attribute_option_url = $Baseurl.'/rest/V1/products/attributes/'.$addAttCode.'/options';
											$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
											$ch = curl_init();
											curl_setopt($ch,CURLOPT_URL, $attribute_option_url);
											curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
											curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
											curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
											$options = curl_exec($ch); 
											curl_close($ch);
											$options_list = json_decode($options, TRUE); 
											//echo "<pre>";print_r($options_list);exit;
											if(sizeof($options_list) > 0){
												foreach($options_list as $optionArr){
													
													$optionlabel = $optionArr['label'];
													$optionvalue = $optionArr['value'];
													if(strtolower($optionlabel) == strtolower($AttValue)){
														$custom_attributes[] = array( 'attribute_code' => $addAtt->key, 'value' =>$optionvalue);
													}
													
												}
												
											}
											
						}	
				}
	   }
	   
	$product_type = strtolower($productallData['prdType']);
			
	$sampleProductData = array(
		'sku'               => $productallData['sku'],
		'name'              => $productallData['prdName'],
		'visibility'        => $productallData['prdVisibility'], /*'catalog',*/
		'typeId'           => $product_type,
		'price'             => $productallData['prdPrice'],
		'status'            => $productallData['prdStatus'],
		'attributeSetId'  => 4,
		'weight'            => $productallData['prdWeight'],
		'custom_attributes' => $custom_attributes,
		'media_gallery_entries'=>$mediaGalleryEntries,   //mediaGalleryEntries
		'extension_attributes' => array(
				"stockItem"=>array(
					'qty'=>$qty,
					'isInStock'=>$productallData['prdInStock'],
					'manageStock'=>$prdInStock,
					/*'min_qty'=>$prdMngStock,
					'use_config_manage_stock' => $prdConfigMngStock,
					'min_qty' => $prdMinQty,
					'use_config_min_qty' => $prdConfigMinQty,
					'min_sale_qty' => $prdMinSaleQty,
					'use_config_min_sale_qty' => $prdConfigMinSaleQty,
					'max_sale_qty' => $prdMaxSaleQty,
					'use_config_max_sale_qty' => $prdConfigMaxSaleQty*/
				),
		),
		'tierPrices'=>$tierPricesFinalArr,
	);
	
	//configurableProductOptions
	$associated_products = array();
	
	if($update_array['prdName'] == 0){
		unset($sampleProductData['name']);
	}
	if($update_array['prdPrice'] == 0){
		unset($sampleProductData['price']);
	}
	if($update_array['prdQuantity'] == 0){
		unset($sampleProductData['extension_attributes']['stockItem']['qty']);
	}	
	if($update_array['prdWeight'] == 0){
		unset($sampleProductData['weight']);
	}
	if($update_array['prdStatus'] == 0){
		unset($sampleProductData['status']);
	}
	if($update_array['prdVisibility'] == 0){
		unset($sampleProductData['visibility']);
	}if($update_array['prdImage'] == 0){
		unset($sampleProductData['media_gallery_entries']);
	}
	//print_r($sampleProductData);exit;
	$productData = json_encode(array('product' => $sampleProductData));
	$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $product_url);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $productData);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch); 
	curl_close($ch);
	 $data = json_decode($response, TRUE); //echo "<pre>";print_r($data);exit;
	$prd_id = isset($data['id']) ? $data['id'] : '';
	$prd_sku = isset($data['sku']) ? $data['sku'] : '';
	
	if($product_type == 'configurable'){
		SetAssociateProducts($sku,$productallData,$Baseurl, $adminToken);
	}
	
	if($prd_id !=''){
		
		
		$returnArr = array("status" =>'1', "product_id"=>$prd_id,"hosted_image_url"=>$url,"image_Posted_url"=>$url, "message"=>'Product updated successfully', 'new_ceated_imgs'=>$imagename,'NewImage'=>$imagename);
		return $returnArr;
		
	
	}
	else{
		$returnArr = array("status" =>'0', "message"=>$data);
		return $returnArr;
	}
}

function createproduct($adminToken,$product_url,$productallData,$Baseurl){
	
	
	$prdSpecialPrice = isset( $productallData['prdSpecialPrice']) ? $productallData['prdSpecialPrice'] : '';
	$prdSpecialFromDate = isset( $productallData['prdSpecialFromDate']) ? $productallData['prdSpecialFromDate'] : '';
	$prdSpecialToDate = isset( $productallData['prdSpecialToDate']) ? $productallData['prdSpecialToDate'] : '';
	
	
			$category_ids = $productallData['prdCategories'];
			$description = $productallData['prdDesc'];
			$short_description = $productallData['prdShortDesc'];
			$special_price = $prdSpecialPrice;
			$special_from_date = $prdSpecialFromDate;
			$special_to_date = $prdSpecialToDate;
			$tax_class_id = $productallData['prdTaxId'];
		
	
	$prd_imge = $productallData['prdImg'];
	$mediaGalleryEntries = array();
	if(count($prd_imge) > 0){
			
				foreach($prd_imge as $gallery_img){
					
						$url = $gallery_img; 
						$content = file_get_contents($url);
						$image_name = $prd_sku.'_'.time().'.jpg';
						$mediaGalleryEntries[] =  array("id"=>0,"mediaType"=>"image","label"=>$productallData['prdName'],"position"=> 0,"disabled"=>false,
								  "types"=>array("image", "small_image", "thumbnail"),
								  "content"=>array("base64EncodedData"=>base64_encode($content),"type"=>"image/jpeg","name"=>$image_name));
						  
						
				}
	   }
		 
	
	
	$qty  = isset( $productallData['prdQuantity']) ? $productallData['prdQuantity'] : '0';
	$prdInStock  = isset( $productallData['prdInStock']) ? $productallData['prdInStock'] : '0';
	$prdMngStock  = isset( $productallData['prdMngStock']) ? $productallData['prdMngStock'] : '0';
	$prdMinQty  = isset( $productallData['prdMinQty']) ? $productallData['prdMinQty'] : '0';
	$prdConfigMngStock  = isset( $productallData['prdConfigMngStock']) ? $productallData['prdConfigMngStock'] : '0';
	$prdConfigMinQty  = isset( $productallData['prdConfigMinQty']) ? $productallData['prdConfigMinQty'] : '0';
	$prdMinSaleQty  = isset( $productallData['prdMinSaleQty']) ? $productallData['prdMinSaleQty'] : '0';
	$prdConfigMinSaleQty  = isset( $productallData['prdConfigMinSaleQty']) ? $productallData['prdConfigMinSaleQty'] : '0';
	$prdMaxSaleQty  = isset( $productallData['prdMaxSaleQty']) ? $productallData['prdMaxSaleQty'] : '0';
	$prdConfigMaxSaleQty  = isset( $productallData['prdConfigMaxSaleQty']) ? $productallData['prdConfigMaxSaleQty'] : '0';
	$tax_class_id  = isset( $productallData['prdTaxId']) ? $productallData['prdTaxId'] : '0';
	$product_type = $productallData['prdType'];
	$prdUrlPath  = isset( $productallData['prdUrlPath']) ? $productallData['prdUrlPath'] : '';
	if($prdUrlPath == ""){ $prdUrlPath = str_replace(" ","-",$productallData['prdName']); }
	$sku = $productallData['sku'];
	
	if($prdUrlPath == ""){ $prdUrlPath = str_replace(" ","-",$productallData['prdName']); }
	$prdUrlPath = str_replace(" ","-",$productallData['prdName']."-".$sku);
	
	UpdateAttributesForCatelogProduct($productallData,$adminToken,$Baseurl);
	
	$custom_attributes = array( array( 'attribute_code' => 'category_ids', 'value' => $productallData['prdCategories'] ),
			array( 'attribute_code' => 'description', 'value' => $productallData['prdDesc'] ),
			array( 'attribute_code' => 'short_description', 'value' => $productallData['prdShortDesc']),
			array( 'attribute_code' => 'url_key', 'value' => $prdUrlPath),
			array( 'attribute_code' => 'tax_class_id', 'value' =>$tax_class_id));

	if($prdSpecialPrice != "" && $prdSpecialPrice > 0){
		$custom_attributes[] = array( 'attribute_code' => 'special_price', 'value' => $prdSpecialPrice);
	}
    if($prdSpecialFromDate != ""){
		$custom_attributes[] = array( 'attribute_code' => 'special_from_date', 'value' => $prdSpecialFromDate);
	}
	if($prdSpecialToDate != ""){
		$custom_attributes[] = array( 'attribute_code' => 'special_to_date', 'value' => $prdSpecialToDate);
	}	
	
	$attribute_options_values = array();
		$additionalAttributes = $productallData['additionalAttributes'];
		$isNewAttributeCreated = 'No';
	   
	    if(sizeof($additionalAttributes) > 0){
		
			foreach($additionalAttributes as $addAtts){
					
					foreach($addAtts as $addAtt){
				
										$addAtt = (object)($addAtt); 
										
										$addAttCode = $addAtt->key;
										$AttValue = $addAtt->value;
										
										// GET Attorbute options
										$attribute_option_url = $Baseurl.'/rest/V1/products/attributes/'.$addAttCode.'/options';
										$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
										$ch = curl_init();
										curl_setopt($ch,CURLOPT_URL, $attribute_option_url);
										curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
										curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
										curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
										$options = curl_exec($ch); 
										curl_close($ch);
										$options_list = json_decode($options, TRUE); 
										
										$isThisOptionAvailable = 'No';
										
										if(sizeof($options_list) > 0){
											foreach($options_list as $optionArr){
												
												$optionlabel = $optionArr['label'];
												$optionvalue = $optionArr['value'];
												if(strtolower($optionlabel) == strtolower($AttValue)){
													$custom_attributes[] = array( 'attribute_code' => $addAtt->key, 'value' =>$optionvalue);
													$isThisOptionAvailable = 'Yes';
												}
												
											}
											
										}
										
										// Add new Option for Attribute 
										if($isThisOptionAvailable == 'No'){
											
													$optiondata = array(
													   "label" => (string)$addAtt->value,
													   "sortOrder"=> 0,
													   "isDefault"=>false,
													);
													
													// POST Attribute options
													$attribute_option = json_encode(array('option' => $optiondata,'attributeCode'=>$addAttCode));
													$attribute_option_url = $Baseurl.'/rest/all/V1/products/attributes/'.$addAttCode.'/options';
													$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
													$ch = curl_init();
													curl_setopt($ch,CURLOPT_URL, $attribute_option_url);
													curl_setopt($ch,CURLOPT_POSTFIELDS, $attribute_option);
													curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
													curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
													curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
													curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
													$options_id = curl_exec($ch); 
													curl_close($ch);
													$isNewAttributeCreated = 'Yes';
										}
										
					}	
			}
	   }


		 if(sizeof($additionalAttributes) > 0 && $isNewAttributeCreated == 'Yes'){
		
				foreach($additionalAttributes as $addAtts){
						
						foreach($addAtts as $addAtt){
					
											$addAtt = (object)($addAtt); 
											
											$addAttCode = $addAtt->key;
											$AttValue = $addAtt->value;
											
											// GET Attorbute options
											$attribute_option_url = $Baseurl.'/rest/V1/products/attributes/'.$addAttCode.'/options';
											$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
											$ch = curl_init();
											curl_setopt($ch,CURLOPT_URL, $attribute_option_url);
											curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
											curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
											curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
											curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
											$options = curl_exec($ch); 
											curl_close($ch);
											$options_list = json_decode($options, TRUE); 
											
											if(sizeof($options_list) > 0){
												foreach($options_list as $optionArr){
													
													$optionlabel = $optionArr['label'];
													$optionvalue = $optionArr['value'];
													if(strtolower($optionlabel) == strtolower($AttValue)){
														$custom_attributes[] = array( 'attribute_code' => $addAtt->key, 'value' =>$optionvalue);
													}
													
												}
												
											}
											
						}	
				}
	   }
	   
	 $tierPricesFinalArr = array();
	 $tierPriceArr = $productallData['prdGroupPrice'];
		
		if(sizeof($tierPriceArr) > 0){
			
				$tirepriceArr = array();
				foreach($tierPriceArr as $PriceVariations){
					
					$group_id = isset($PriceVariations['groupId']) ?$PriceVariations['groupId'] : '';
					$group_Price = isset( $PriceVariations['price']) ? $PriceVariations['price'] : '';
					$group_qty = isset( $PriceVariations['group_qty']) ? $PriceVariations['group_qty'] : 1;
					
					if($group_id !='' && $group_qty !='' && $group_Price !='')
					{
						$tireprice = array (
								'customer_group_id' => $group_id,
								'qty' => $group_qty,
								'value' => $group_Price,
							);
						
						 $tirepriceArr[] = $tireprice;
					}
				}	
			$tierPricesFinalArr = $tirepriceArr;
		}  
	
	$product_type = strtolower($productallData['prdType']); 	
	
	$sampleProductData = array(
	    'sku'               => $productallData['sku'],
		'name'              => $productallData['prdName'],
		'visibility'        => $productallData['prdVisibility'], /*'catalog',*/
		'typeId'           => $product_type,
		'price'             => $productallData['prdPrice'],
		'status'            => $productallData['prdStatus'],
		'attributeSetId'  => 4,
		'weight'            => $productallData['prdWeight'],
		'custom_attributes' => $custom_attributes,
		'extension_attributes' => array(
				"stockItem"=>array(
					'qty'=>$qty,
					'isInStock'=>$productallData['prdInStock'],
					'manageStock'=>$prdInStock,
				/*'min_qty'=>$prdMngStock,
				'use_config_manage_stock' => $prdConfigMngStock,
				'min_qty' => $prdMinQty,
				'use_config_min_qty' => $prdConfigMinQty,
				'min_sale_qty' => $prdMinSaleQty,
				'use_config_min_sale_qty' => $prdConfigMinSaleQty,
				'max_sale_qty' => $prdMaxSaleQty,
				'use_config_max_sale_qty' => $prdConfigMaxSaleQty*/
			),
		),
		'tierPrices'=>$tierPricesFinalArr
		
		
	);
	
	if(sizeof($mediaGalleryEntries) > 0){
		$sampleProductData['media_gallery_entries'] = $mediaGalleryEntries;
	}
	
	
	/*if($group_id !='' && $group_qty !='' && $group_Price !='')
	{
		$tireprice =array(
			array (
				'customer_group_id' => $group_id,
				'qty' => $group_qty,
				'value' => $group_Price,
			),
		);
		$sampleProductData['tier_prices']=$tireprice;
		
	}*/
	
	$productData = json_encode(array('product' => $sampleProductData));
	$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $product_url);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $productData);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch); 
	curl_close($ch);
	 $data = json_decode($response, TRUE);
	$prd_id = isset($data['id']) ? $data['id'] : '';
	$prd_sku = isset($data['sku']) ? $data['sku'] : '';
	
	
	//configurableProductOptions
	$associated_products = array();
	if($product_type == 'configurable'){
		
		SetAssociateProducts($sku,$productallData,$Baseurl, $adminToken);
			
	}
	
	if($prd_id !=''){
		
		$returnArr = array("status" =>'1', "product_id"=>$prd_id,"hosted_image_url"=>$url,"image_Posted_url"=>$url, "message"=>'Product created successfully', 'new_ceated_imgs'=>$imagename,'NewImage'=>$imagename);
		return $returnArr;
		
	
	}
	else{
		$returnArr = array("status" =>'0', "message"=>$data);
		return $returnArr;
	}
}


function SetAssociateProducts($sku,$productallData,$Baseurl, $adminToken){
	
		$associated_skus = $productallData['associatedSKUsData']; 
			//array_push($productData['associated_skus'],'attributes');	

			if(sizeof($associated_skus) > 0){
					 
					foreach($productallData['associatedSKUsData'] as $attsArr) {
						//print_r($attsArr);
						$associteSKU = $attsArr['sku'];
						 $productId = getProductIDfromSku($associteSKU);
						
						foreach ($attsArr as $AttKey=>$AttValue) {
							
							if($AttKey != "sku"){
								   
									$attributeDetails = getAttributeIdFromAttrOptions($AttKey,$AttValue,$Baseurl, $adminToken);
									//print_r($attributeDetails);
									if(sizeof($attributeDetails) > 0){	
								   
										$attributeId = $attributeDetails['attribute_id'];
										$option_id = isset($attributeDetails['option_id'])?$attributeDetails['option_id']:"";

										if($option_id != ""){	
										
											$optionsArr = array("value_index"=>$option_id);
											$configurableProductOptionsArr = array("productId"=>$productId,"attribute_id"=>$attributeId,"label"=>$AttKey,"position"=>0,"isUseDefault"=>true,"values"=>array($optionsArr));
											$configurableProductOptions[] = $configurableProductOptionsArr;
										}
										
							        }
							  }	
								
						}
						$associated_products[] = $associteSKU;
					}
			}
			
			// Delete exsting options
			
			if(sizeof($configurableProductOptions) > 0){
				
				   $config_product_url = $Baseurl.'/rest/V1/configurable-products/'.$sku.'/options/all';
				    $setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
					$ch = curl_init();
					curl_setopt($ch,CURLOPT_URL, $config_product_url);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
					curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$exsting_options = curl_exec($ch); 
					curl_close($ch);
					$exsting_options_arr = json_decode($exsting_options);
					
					if(sizeof($exsting_options_arr) > 0){
						
						foreach($exsting_options_arr as $optionsObj){
							
								$options_id = $optionsObj->id;
								$config_product_url = $Baseurl.'/rest/V1/configurable-products/'.$sku.'/options/'.$options_id;
								$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
								$ch = curl_init();
								curl_setopt($ch,CURLOPT_URL, $config_product_url);
								curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
								curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								$response = curl_exec($ch); 
								curl_close($ch);
							
						}

						
					}
					
					$config_product_url = $Baseurl.'/rest/V1/configurable-products/'.$sku.'/options';
					
					foreach($configurableProductOptions as $config_options){
							
							$config_product_options = json_encode(array('option'=>$config_options));
							$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
							$ch = curl_init();
							curl_setopt($ch,CURLOPT_URL, $config_product_url);
							curl_setopt($ch,CURLOPT_POSTFIELDS, $config_product_options);
							curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
							curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							$response = curl_exec($ch); 
							curl_close($ch);
						
					}
				
			}
			
			if(sizeof($associated_products) > 0){
				
					$child_product_url = $Baseurl.'/rest/V1/configurable-products/'.$sku.'/child';
					foreach($associated_products as $child_sku){
							$child_product = json_encode(array('childSku' => $child_sku));
							$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
							$ch = curl_init();
							curl_setopt($ch,CURLOPT_URL, $child_product_url);
							curl_setopt($ch,CURLOPT_POSTFIELDS, $child_product);
							curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
							curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							$response = curl_exec($ch); 
							curl_close($ch);
						
					}
			}	
	
}


function createproductoption($adminToken,$product_option_url,$productallData){
	$productOptionData = $productallData['custom_option_fields'];
	$sku = $productallData['sku'];
	$ch = curl_init();
	
	if(sizeof($productOptionData) > 0){
		
			foreach($productOptionData as $productcustiomoption){
				$addtion_infos = $productcustiomoption['additional_fields'];
			
				$sampleProductData =array(
					'product_sku'        => $sku,
					'title'              => $productcustiomoption['title'],
					'type'               => $productcustiomoption['type'],
					'is_require'         => TRUE,
					'sort_order'         =>'1',
					'sku'               => $sku,
					'option_id'			=>'0',
					'values'			=>$addtion_infos
				);
				//print_r($sampleProductData);exit;
				//$sampleProductData['values']=$addtion_infos;
				
				$productData = json_encode(array('option' => $sampleProductData));
				$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
				curl_setopt($ch,CURLOPT_URL, $product_option_url);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $productData);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($ch);
				$data = json_decode($response, TRUE);
			}
	}	
	curl_close($ch);
	
	
}


function downloadproduimage($url,$prd_sku){
	$prd_sku = str_replace(' ', '-', $prd_sku);
	$prd_sku = strtolower($prd_sku);
	$image_name = $prd_sku.'.jpg';
	$imagepath = getcwd().'/../pub/media/import/'.$image_name;
	$content = file_get_contents($url);
	file_put_contents($imagepath, $content);
	return $image_name;

}


function createAttributeDetailsByCode($attributeCode, $Baseurl, $adminToken){
	
					// GET Attorbute options
					$attData = json_encode(array("attributeSetId"=>0,"attributeGroupId"=>0,"attributeCode"=>$attributeCode,"sortOrder"=>0));
					$attribute_url = $Baseurl.'/rest/V1/products/attributes';
					$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
					$ch = curl_init();
					curl_setopt($ch,CURLOPT_URL, $attribute_url);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
					curl_setopt($ch,CURLOPT_POSTFIELDS, $attData);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$options = curl_exec($ch); 
					curl_close($ch);
					$res = json_decode($options, TRUE); 
					
		return 1;
}

function getAttributeDetailsByCode($attributeCode){
	
	$attributeCode = $attributeCode;
	$entityType = 'catalog_product';
	$attributeId = 0; 
	$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
	 
	try{
			$attributeInfo = $objectManager->get(\Magento\Eav\Model\Entity\Attribute::class)->loadByCode($entityType, $attributeCode);
			$attributeId = $attributeInfo->getAttributeId();	
	}catch(Exception $e){
		
	}
	
	return $attributeId;
}


function getProductIDfromSku($sku){
	
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$product = $objectManager->get('Magento\Catalog\Model\Product');
	$pid = $product->getIdBySku($sku);
	
	if($pid){
		return $pid; 
	}
	return 0;

}

function checkProductExist($sku){
	
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$product = $objectManager->get('Magento\Catalog\Model\Product');

	if($product->getIdBySku($sku)) {
		return 1; 
	}
	return 0;

}

function saveproductimage($imagename,$prd_id){
	 
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
	$productId = $prd_id; 
	$product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
	$imagePath = '/home/sevencha/public_html/magento-60914-286844.cloudwaysapps.com/apis/pub/media/import/'.$imagename;
	$product->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false);
	$product->save();
		
	return 1;

}

function getAttributeIdFromAttrOptions($addAttCode, $AttValue,$Baseurl, $adminToken){
	
		$return_arr = array();
		$attribute_id = getAttributeDetailsByCode($addAttCode);
		
		if($attribute_id > 0){
			
					// GET Attorbute options
					$attribute_option_url = $Baseurl.'/rest/V1/products/attributes/'.$addAttCode.'/options';
					$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
					$ch = curl_init();
					curl_setopt($ch,CURLOPT_URL, $attribute_option_url);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
					curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$options = curl_exec($ch); 
					curl_close($ch);
					$options_list = json_decode($options, TRUE); 
					
						if(sizeof($options_list) > 0){
							foreach($options_list as $optionArr){
								
								$optionlabel = $optionArr['label'];
								$optionvalue = $optionArr['value'];
								if(strtolower($optionlabel) == strtolower($AttValue)){
									//$custom_attributes[] = array( 'attribute_code' => $addAtt->key, 'value' =>$optionvalue);
									$option_id = $optionvalue;
								}
								
							}
							
						}
						$return_arr['attribute_id'] = $attribute_id;
						$return_arr['option_id'] = $option_id;
			}
			
		return $return_arr;
}

function deleteCatelogProduct($prdData,$adminToken,$Baseurl){
	
		$sku = $prdData['sku']; 
		$product_url = $Baseurl. "rest/V1/products/".$sku;
	
		$productData = json_encode(array('sku'=>$sku));
		$ch = curl_init();
	
		$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
		curl_setopt($ch,CURLOPT_URL, $product_url);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $productData);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		
		$data = json_decode($response, TRUE); 
	if($data){
		$returnArr = array("status" =>'1', "sku"=>$sku, "message"=>'Product deleted successfully');
		return $returnArr;
	}else{
		return $returnArr = array("status" =>'0', "sku"=>$sku, "message"=>'Error : Something was wrong');
	}
	
}


function UpdateAttributesForCatelogProduct($prdData,$adminToken,$Baseurl){
	
   $sku = $prdData['sku']; 
		
	foreach($prdData['additionalAttributes'] as $addAtts){
			
			foreach($addAtts as $addAtt){
				
										$addAtt = (object)($addAtt); 
										$addAttCode = $addAtt->key;
										$addtributeID = getAttributeDetailsByCode($addAttCode);
										
										if($addtributeID <= 0){
										
												$label = array (
												   array(
													"store_id" => array("0"),
													"value" => $addAtt->value
													)
												);
												
												$options = array(
												   "label" => $label,
												   "order" => "1",
												   "is_default" => "1",
												   "storeLabels"=>$label 
												);
												//print_r($options);
												$attData = array(
												   "attributeSetId" =>"0",	
												   "attributeGroupId" =>"0",
												   "attributeCode" => $addAtt->key,
												   "frontend_input" => "select",
												   "sortOrder"=>"0",
												   "scope" => "global",
												   "defaultValue" => "0",
												   "isUnique" => 0,
												   "isRequired" => 0,
												   "applyTo" => array("simple","grouped","configurable","virtual","bundle","downloadable"),
												   "isConfigurable" => 1,
												   "isSearchable" => 1,
												   "isVisible_in_advanced_search" => 1,
												   "isComparable" => 1,
												   "isUsedForPromoRules" => 1,
												   "isVisibleOnFront" => 1,
												   "usedInProductListing" => 1,
												   "additionalFields" => array(),
												   "frontendLabel" => array(array("store_id" => "0", "label" => $addAtt->key)),
												   "options"=> $options,
												   "frontendLabels"=> $label,
												);
												
												$att_post_url = $Baseurl. "rest/V1/products/attributes";
												
												$attData = json_encode(array("attribute"=>$attData));
												$ch = curl_init();
											
												$setHaders = array('Content-Type:application/json','Authorization:Bearer '.$adminToken);
												curl_setopt($ch,CURLOPT_URL, $att_post_url);
												curl_setopt($ch,CURLOPT_POSTFIELDS, $attData);
												curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
												curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
												curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
												$response = curl_exec($ch);
											 $data = json_decode($response, TRUE);
										}
											 
							
			}
		}
		
	
	
}

function GetProductList($prdData,$adminToken,$Baseurl)
{
	$sku = $prdData['sku']; 
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
	$product = $objectManager->create('Magento\Catalog\Model\Product')->loadBySku($sku);
	
	$product_id = $product->getId();
	if($product_id <= 0){
		return array("status" =>'0', "message"=>'Product not exist');
	}
			/*$info = new stdclass();
            $info->attributes = array('sku', 'name', 'description', 'price', 'url_path');
            $product = $client->catalogProductInfo($sessionId, $product_id, NULL, $info);
	        $name = $product->name;
            $description = $product->description;
            $price = $product->price;
			$formatted_price = number_format($price, 2);    
            $image_url = $image[0]->url;*/
            $product_url = $product->url_path;
           
            $sku = $product->sku;
			
	return  array("product_url" =>$product_url, "message"=>'Product exist');
}
?>