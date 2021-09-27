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

namespace Webkul\MobikulApi\Controller\Checkout;

/**
 * Class AddToCart api
 */
class AddToCart extends AbstractCheckout
{

    public function execute()
    {
        $this->verifyRequest();
        try {
            if ($this->wholeData) {
                
                $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
                $quote = new \Magento\Framework\DataObject();
                // added check for expired quote ////////////////////////////////////
                if ($this->quoteId) {
                    $size = $this->quoteFactory
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter("entity_id", ["eq" => $this->quoteId])
                        ->getSize();
                    if (!$size) {
                        $this->quoteId = 0;
                    }
                }
                if ($this->customerId == 0 && $this->quoteId == 0) {
                    $this->returnArray["message"]="Please login to buy membership plan.";
                    return $this->getJsonResponse($this->returnArray);
                }
                
                // end added check for expired quote ////////////////////////////////
                if ($this->customerId == 0 && $this->quoteId == 0) {
                    $this->setQuoteIdData();
                }
                if ($this->qty == 0) {
                    $this->qty = 1;
                }
                if ($this->customerId != 0) {
                    $this->saveQuoteCustomerData();
                }
                $quote = $this->helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
                $product = $this->productFactory->create()->setStoreId($this->storeId)->load($this->productId);
                if ($this->qty && !($product->getTypeId() == "downloadable")) {
                    $this->checkStockData($product);
                }
                if($this->qty && ($product->getTypeId() == "Membership")){
                    $this->validateMembershipItem($quote, $product, $this->customerId, $this->qty);
                }
                $itemss = $quote->getAllItems();
                foreach($itemss as $iteme){
                    if($iteme->getProductType() == "Membership") {
                        $this->returnArray["message"] = __('Subscription Products must be bought separately as a single item in the shopping cart. Please add any extra items to your wish list to buy in a separate transaction.');
                        return $this->getJsonResponse($this->returnArray);
                    }
                }
                $request = [];
                $paramOption = [];
                $filesToDelete = [];
                // if (isset($this->params["options"])) {
                $request = $this->setProductParamOptions($product);
                // }
                $request["qty"] = $this->qty;
                $request = $this->_getProductRequest($request);
                
                if($product->getTypeId() == "Membership"){
                    // $request['info_buyRequest']['duration_option']=array(
                    //     'duration' => $this->duration,
                    //     'duration_unit' => $this->durationunit
                    // );
                    // $itemss = $quote->getAllItems();
                    // foreach($itemss as $item){
                    //     if($item->getProductType() == "Membership") {
                            
                            // $buyRequest = $this->serializer->unserialize($item->getOptionByCode('info_buyRequest')->getValue());
                            // $buyRequest['duration_option']['duration'] = $this->duration;
                            // $buyRequest['duration_option']['duration_unit'] = $this->durationunit;
                            // $item->getOptionByCode('info_buyRequest')->setValue($this->serializer->serialize($buyRequest));
                            // $item->saveItemOptions();
                            // $itemId=$item->getId();
                            // $quote->updateItem($itemId, $buyRequest);
                            // $this->quoteRepository->save($quote);
                        // }
                    // }
                    
                    

                    $additionalOptions = [];
                     $additionalOptions["duration"] = array(
                         'label' => "Duration",
                         'value' => $this->duration,
                    );
                    $additionalOptions["duration_unit"] = array(
                         'label' => "Duration Unit",
                         'value' => $this->durationunit,
                     );
                     
                    $pricenew=(float) $this->durationoptionprice;
                    $pricenew = round($pricenew,2);
                     $additionalOptions["duration_price"] = array(
                        'label' => "Duration Price",
                        'value' => $pricenew,
                    );
                    $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));

                    //$objectManagers = \Magento\Framework\App\ObjectManager::getInstance();
                    //$itemee = $objectManagers->create('Magento\Quote\Model\Quote\Item');
                    //$itemee->setQuote($quote)->setProduct($product)->setQty($this->qty);
                    //$infoBuyRequest = [
                    //     "info_buyRequest" => [
                        
                    //             "duration" => $this->duration,
                    //             "duration_unit" => $this->durationunit
                        
                    //     ]
                    // ];
                    
                    // $options = $objectManagers->create(
                    //     \Magento\Quote\Model\Quote\Item\Option::class,
                    //     ['data' => $infoBuyRequest]
                    // );
                    // $itemee->addOption($options);
                    // $itemee->setProductOptions($infoBuyRequest);
                    
                }
                $productAdded = $quote->addProduct($product, $request);

                if($productAdded && ($product->getTypeId() == "Membership")){
                    $newitems = $quote->getAllItems();
                    foreach($newitems as $item){
                        //$this->returnArray["message"]="Inside condition";
                        //return $this->getJsonResponse($this->returnArray);
                        
                        $price=(float) $this->durationoptionprice;
                        $price = round($price,2);
                        $item->setCustomPrice($price);
                        $item->setOriginalCustomPrice($price);
                        $item->getProduct()->setIsSuperMode(true);
                        $item->setRowTotal($this->qty*$price);
                        
                        $item->save();
                        //$quote->setTriggerRecollect(1);
                        //$quote->collectTotals()->save();
                    }
                }

                $result = $this->checkRelatedProduct($quote);
                $allAdded = $result['allAdded'];
                $allAvailable = $result['allAvailable'];
                $quote->setTriggerRecollect(1);
                $quote->getBillingAddress();
                $quote->collectTotals()->save();
                if (!$productAdded || is_string($productAdded)) {
                    $this->returnArray["message"] = __("Unable to add product to cart.");
                    if (is_string($productAdded)) {
                        $this->returnArray["message"] = $productAdded;
                    }
                    return $this->getJsonResponse($this->returnArray);
                } else {
                    $this->returnArray["cartCount"] = $this->helper->getCartCount($quote);
                }
                $this->returnArray["message"] = htmlspecialchars_decode(
                    __("You added %1 to your shopping cart.", $this->helperCatalog->stripTags($product->getName()))
                );
                if (!$allAvailable) {
                    $this->returnArray["message"] .= __(" but, We don't have some of the products you want.");
                }
                if (!$allAdded) {
                    $this->returnArray["message"] .= __(" but, We don't have as many of some products as you want.");
                }
                // delete files uploaded for custom option /////////////////
                foreach ($filesToDelete as $eachFile) {
                    $this->fileDriver->deleteFile($eachFile);
                }
                $this->returnArray["isVirtual"] = (bool)$quote->getIsVirtual();
                $this->returnArray["success"] = true;
                $this->emulate->stopEnvironmentEmulation($environment);
                $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
                return $this->getJsonResponse($this->returnArray);
            } else {
                $this->returnArray["message"] = __("Invalid Request");
                $this->helper->log($this->returnArray, "logResponse", $this->wholeData);
                return $this->getJsonResponse($this->returnArray);
            }
        } catch (\Exception $e) {
            if ($e->getMessage() != "") {
                $this->returnArray["message"] = $e->getMessage();
            } else {
                $this->returnArray["message"] = __("Can't add the item to shopping cart.");
            }
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage() != "") {
                $this->returnArray["message"] = $e->getMessage();
            } else {
                $this->returnArray["message"] = __("Can't add the item to shopping cart.");
            }
            $this->_helper->printLog($returnArray, 1);
            return $this->getJsonResponse($this->returnArray);
        }
    }
    public function validateMembershipItem($quote, $product, $customerId, $qty){

        if($customerId == 0){
            $this->returnArray["message"]="Please login to buy membership plan.";
            return $this->getJsonResponse($this->returnArray);
       } 
       else{
            $model = $this->_MembershipOrdersFactory->create()->getCollection();
            $model->addFieldToFilter('customer_id', $customerId);
            $model->addFieldToFilter('order_status', 'complete');
            $model->addFieldToFilter('plan_expiry_status', 0);
            $model->setOrder('membership_order_id', 'DESC');
            if($product->getTypeId() == "Membership" && count($model) > 0){
                $this->returnArray["message"]=__('You have already purchased membership plan.');
                return $this->getJsonResponse($this->returnArray); 
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $sellerModels = $objectManager->get('Webkul\Marketplace\Model\Seller');
            $sellerdata = $sellerModels->load ($customerId, 'customer_id' );
            if(count($sellerdata->getData()) > 1 && $sellerdata->getStatus() == 1 && $product->getTypeId() == "Membership" ){
                $this->returnArray["message"]=__('No Prime Membership for Suppliers. They will need to open a customers account to buy a Prime Subscription. ');
                return $this->getJsonResponse($this->returnArray);
            }
            $items = $quote->getAllItems();
                        
            $size = count($items);
            if($size > 0){
                $this->returnArray["message"]="Membership Plan is not allowed to purchase with other products. Kindly do seprate transaction.";
                return $this->getJsonResponse($this->returnArray); 
            }
            else{
                if($qty > 1){
                    $this->returnArray["message"]="Membership Plan is not allowed to purchase with more than 1 quantity.";
                    return $this->getJsonResponse($this->returnArray);
                }
            }
       }
    }
    protected function checkRelatedProduct($quote)
    {
        $allAdded = true;
        $allAvailable = true;
        if (!empty($this->relatedProducts)) {
            foreach ($this->relatedProducts as $productId) {
                $productId = (int)$productId;
                if (!$productId) {
                    continue;
                }
                $relatedProduct = $this->productFactory->create()->setStoreId($this->storeId)->load($productId);
                if ($relatedProduct->getId() && $relatedProduct->isVisibleInCatalog()) {
                    try {
                        $quote->addProduct($relatedProduct);
                    } catch (\Exception $e) {
                        $allAdded = false;
                    }
                } else {
                    $allAvailable = false;
                }
            }
        }
        return ['allAdded'=>$allAdded, 'allAvailable'=>$allAvailable];
    }

    protected function setProductParamOptions($product)
    {
        $request = [];
        $paramOption = [];
        if (isset($this->params["options"])) {
            $productOptions = $this->params["options"];
            foreach ($productOptions as $optionId => $values) {
                $option = $this->productOption->load($optionId);
                $optionType = $option->getType();
                if (in_array($optionType, ["multiple", "checkbox"])) {
                    foreach ($values as $optionValue) {
                        $paramOption[$optionId][] = $optionValue;
                    }
                } elseif (in_array($optionType, ["radio", "drop_down", "area", "field"])) {
                    $paramOption[$optionId] = $values;
                } elseif ($optionType == "file") {
                    // downloading file /////////////////////////////////////////////
                    $base64String = $productOptions[$optionId]["encodeImage"];
                    $fileName = time().$productOptions[$optionId]["name"];
                    $fileType = $productOptions[$optionId]["type"];
                    $fileWithPath = $this->helperCatalog->getBasePath().DS.$fileName;
                    $ifp = $this->fileDriver->fileOpen($fileWithPath, "wb");
                    $this->fileDriver->fileWrite($ifp, $this->base64Json->unserialize($base64String));
                    // assigning file to option /////////////////////////////////////
                    $fileOption = [
                        "type" => $fileType,
                        "title" => $fileName,
                        "quote_path" => DS."media".DS.$fileName,
                        "fullpath" => $fileWithPath,
                        "secret_key" => substr(hash("sha256", $this->fileDriver->fileGetContents($fileWithPath)), 0, 20)
                    ];
                    $filesToDelete[] = $fileWithPath;
                    $paramOption[$optionId] = $fileOption;
                } elseif ($optionType == "date") {
                    $paramOption[$optionId]["day"] = $values["day"];
                    $paramOption[$optionId]["year"] = $values["year"];
                    $paramOption[$optionId]["month"] = $values["month"];
                    if ($this->helperCatalog->useCalenderForCustomOption()) {
                        $paramOption[$optionId]["date"] = $this->getCustomOptionDate($values);
                    }
                } elseif ($optionType == "date_time") {
                    $paramOption[$optionId]["day"] = $values["day"];
                    $paramOption[$optionId]["year"] = $values["year"];
                    $paramOption[$optionId]["hour"] = $values["hour"];
                    $paramOption[$optionId]["month"] = $values["month"];
                    $paramOption[$optionId]["minute"] = $values["minute"];
                    $paramOption[$optionId]["dayPart"] = $values["day_part"];
                    if ($this->helperCatalog->useCalenderForCustomOption()) {
                        $paramOption[$optionId]["date"] = $this->getCustomOptionDate($values);
                    }
                } elseif ($optionType == "time") {
                    $paramOption[$optionId]["hour"] = $values["hour"];
                    $paramOption[$optionId]["minute"] = $values["minute"];
                    $paramOption[$optionId]["dayPart"] = $values["day_part"];
                }
            }
        }
        if ($product->getTypeId() == "downloadable") {
            if (isset($this->params["links"])) {
                $request = ["links"=>$this->params["links"], "options"=>$paramOption, "product"=>$this->productId];
            } else {
                $request = ["options"=>$paramOption, "product"=>$this->productId];
            }
        } elseif ($product->getTypeId() == "grouped") {
            if (isset($this->params["super_group"])) {
                $request = ["super_group"=>$this->params["super_group"], "product"=>$this->productId];
            }
        } elseif ($product->getTypeId() == "configurable") {
            if (isset($this->params["super_attribute"])) {
                $request = [
                    "super_attribute"=>$this->params["super_attribute"],
                    "options"=>$paramOption,
                    "product"=>$this->productId
                ];
            }
        } elseif ($product->getTypeId() == "bundle") {
            if (isset($this->params["bundle_option"]) && isset($this->params["bundle_option_qty"])) {
                $this->getBundleOption($product);
                $request = [
                    "bundle_option"=>$this->params["bundle_option"],
                    "bundle_option_qty"=>$this->params["bundle_option_qty"],
                    "options"=>$paramOption,
                    "product"=>$this->productId
                ];
            }
        } else {
            $request = ["options"=>$paramOption, "product"=>$this->productId];
        }
        return $request;
    }

    public function getBundleOption($product)
    {
        $this->coreRegistry->register("product", $product);
        $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
            $product->getTypeInstance(true)->getOptionsIds($product),
            $product
        );
        foreach ($selectionCollection as $option) {
            $selectedOptions = $this->params["bundle_option"][$option->getOptionId()] ?? 0;
            if (!empty($selectedOptions) &&
                (
                    $option->getSelectionId() == $selectedOptions ||
                    (is_array($selectedOptions) && in_array($option->getSelectionId(), $selectedOptions))
                )
            ) {
                $selectionQty = $option->getSelectionQty() * 1;
                $key = $option->getOptionId();
                if (isset($this->params["bundle_option_qty"][$key])) {
                    $probablyRequestedQty = $this->params["bundle_option_qty"][$key];
                }
                if ($selectionQty > 1) {
                    $requestedQty = $selectionQty * $this->qty;
                } elseif (isset($probablyRequestedQty)) {
                    $requestedQty = $probablyRequestedQty * $this->qty;
                } else {
                    $requestedQty = 1;
                }
                $associateBundleProduct = $this->productFactory->create()->load($option->getProductId());
                $availableQty = $this->stockRegistry->getStockItem($associateBundleProduct->getId())->getQty();
                if ($associateBundleProduct->getIsSalable()) {
                    if ($requestedQty > $availableQty) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("The requested quantity of %1 is not available", $option->getName())
                        );
                    }
                }
            }
        }
    }

    /**
     * Get Custom Option Date
     *
     * @param array $values
     *
     * @return string
     */
    public function getCustomOptionDate($values)
    {
        $date = [];
        $dateOrder = explode(",", $this->helper->getConfigData("catalog/custom_options/date_fields_order"));
        foreach ($dateOrder as $order) {
            if ($order == "m") {
                $date[] = $values["month"];
            } elseif ($order == "d") {
                $date[] = $values["day"];
            } else {
                $date[] = $values["year"];
            }
        }
        return implode("/", $date);
    }

    protected function checkStockData($product)
    {
        $stockData = $this->stockRegistry->getStockItem($product->getId());
        $availableQty = $stockData->getQty();
        $manageStock = $stockData->getManageStock();
        if ($this->qty <= $availableQty) {
            $filter = new \Magento\Framework\Filter\LocalizedToNormalized(
                ["locale"=>$this->localeResolver->getLocale()]
            );
            $this->qty = $filter->filter($this->qty);
        } else {
            if (!in_array($product->getTypeId(), ["grouped", "configurable", "bundle"]) && $manageStock) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The requested quantity is not available")
                );
            }
        }
    }
    
    protected function setQuoteIdData()
    {
        $quote = $this->quoteFactory->create()
            ->setStoreId($this->storeId)
            ->setIsActive(true)
            ->setIsMultiShipping(false)
            ->save();
        $quote->getBillingAddress();
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals()->save();
        $this->quoteId = (int) $quote->getId();
        $this->returnArray["quoteId"] = $this->quoteId;
    }

    protected function saveQuoteCustomerData()
    {
        $quote = $this->helper->getCustomerQuote($this->customerId);
        $this->quoteId = $quote->getId();
        if ($quote->getId() < 0 || !$this->quoteId) {
            $quote = $this->quoteFactory->create()
                ->setStoreId($this->storeId)
                ->setIsActive(true)
                ->setIsMultiShipping(false)
                ->save();
            $this->quoteId = (int) $quote->getId();
            $customer = $this->customerRepository->getById($this->customerId);
            $quote->assignCustomer($customer);
            $quote->setCustomer($customer);
            $quote->getBillingAddress();
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals()->save();
        }
        if ($quote->getIsVirtual()) {
            $returnArray["isVirtual"] = (bool)$quote->getIsVirtual();
        }
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->qty = $this->wholeData["qty"] ?? 1;
            $this->params = $this->wholeData["params"] ?? "{}";
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->relatedProducts = $this->wholeData["relatedProducts"] ?? "[]";
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->durationoptionprice = $this->wholeData["durationoptionprice"] ?? 0;
            $this->duration = $this->wholeData["duration"] ?? 0;
            $this->durationunit = $this->wholeData["durationunit"] ?? "";
            $this->params = $this->jsonHelper->jsonDecode($this->params);
            $this->relatedProducts = $this->jsonHelper->jsonDecode($this->relatedProducts);
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function to get Product request
     *
     * @param object $requestInfo requestInfo
     *
     * @return object
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(["qty"=>$requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("We found an invalid request for adding product to quote.")
            );
        }
        $this->getRequestInfoFilter()->filter($request);
        return $request;
    }

    /**
     * Function getRequestInfoFilter
     *
     * @return object filter
     */
    protected function getRequestInfoFilter()
    {
        if ($this->requestInfoFilter === null) {
            $this->requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class
            );
        }
        return $this->requestInfoFilter;
    }

    /**
     * Function to get item Data
     *
     * @param object $quote   quote
     * @param object $product project
     *
     * @return bool
     */
    public function getItemByProduct($quote, $product)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->representProduct($product)) {
                return $item;
            }
        }
        return false;
    }
}
