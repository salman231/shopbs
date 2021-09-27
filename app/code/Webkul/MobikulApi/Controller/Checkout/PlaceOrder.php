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
 * Class PlaceOrder
 * To place order at checkout
 */
class PlaceOrder extends AbstractCheckout
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $this->helper->getCustomerQuote($this->customerId);
            }
            if ($this->quoteId != 0) {
                $quote = $this->helper->getQuoteById($this->quoteId)->setStoreId($this->storeId);
            }
            if ($quote->getItemsQty()*1 == 0) {
                $this->returnArray["message"] = __("Sorry Something went wrong !!");
                return $this->getJsonResponse($this->returnArray);
            } else {
                $this->returnArray["cartCount"] = $quote->getItemsQty()*1;
            }
            $selectedShipping = $quote->getShippingAddress()->getShippingMethod();
            // Set Billing Address in quote /////////////////////////////////////////
            $this->setBillingDataInQuote($quote);
            // set payment information in quote /////////////////////////////////////
            $this->savePaymentInfo($quote);
            /**
             * set shipping method again to the new address
             */
            $quote->getShippingAddress()->setShippingMethod($selectedShipping);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals()->save();
            
            // validate minimum amount check ////////////////////////////////////////
            $isCheckoutAllowed = $quote->validateMinimumAmount();
            if (!$isCheckoutAllowed) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($this->helper->getConfigData("sales/minimum_order/description"))
                );
            }
            if ($quote->getCheckoutMethod() == "guest" &&
                !$this->checkoutHelper->isAllowedGuestCheckout($quote, $quote->getStoreId())
            ) {
                $this->returnArray["message"] = __("Guest Checkout is not Enabled");
                return $this->getJsonResponse($this->returnArray);
            }
            $isNewCustomer = false;
            switch ($quote->getCheckoutMethod()) {
                case "guest":
                    $quote = $this->prepareGuestQuote($quote);
                    break;
                case "register":
                    $quote = $this->prepareNewCustomerQuote($quote);
                    $isNewCustomer = true;
                    break;
                default:
                    $quote = $this->prepareCustomerQuote($quote, $this->customerId);
                    break;
            }
            $remoteAddress = $this->remoteAddress->getRemoteAddress();
            if ($remoteAddress) {
                $quote->setRemoteIp($remoteAddress);
                $xForwardIp = $this->getRequest()->getServer('HTTP_X_FORWARDED_FOR');
                $quote->setXForwardedFor($xForwardIp);
            }
            $quote->collectTotals()->save();
            $order = $this->quoteManagement->submit($quote);
            if ($order->getCustomerIsGuest()) {
                $tokenCollection = $this->deviceToken->getCollection()->addFieldToFilter("token", $this->token);
                foreach ($tokenCollection as $eachToken) {
                    $eachToken->setEmail($order->getCustomerEmail());
                    $eachToken->setId($eachToken->getId());
                    $eachToken->save();
                }
            }
            if ($isNewCustomer) {
                $result = $this->involveNewCustomer($quote);
                if (!$result["status"]) {
                    $this->returnArray["message"] = $result["message"];
                    return $this->getJsonResponse($this->returnArray);
                }
            }
            if ($order) {
                $this->eventManager->dispatch(
                    "checkout_type_onepage_save_order_after",
                    ["order"=>$order, "quote"=>$quote]
                );
                $this->saveMobikulOrder($order);
                try {
                    // $this->orderEmailSender->send($order);
                } catch (\Exception $e) {
                    $this->returnArray["message"] = $e->getMessage();
                    return $this->getJsonResponse($this->returnArray);
                }
            }
            $this->eventManager->dispatch("checkout_submit_all_after", ["order"=>$order, "quote"=>$quote]);
            // checknig wheather user is new user or returned customer //////////////
            $userEmail = $order->getCustomerEmail();
            $websiteId = $this->store->load($order->getStoreId())->getWebsiteId();
            $customerModel = $this->customerFactory->create();
            $customer = $customerModel->setWebsiteId($websiteId)->loadByEmail($userEmail);
            if (!$customer->getId()) {
                $this->returnArray["showCreateAccountLink"] = true;
            }
            $this->returnArray["email"] = $userEmail;
            if ($this->salesHelper->canReorder($order->getEntityId())) {
                $this->returnArray["canReorder"] = true;
            }
            $quote->collectTotals()->setIsActive(0)->setReservedOrderId(null)->save();
            $this->getCustomerDetails($order);
            $this->savePurchasePointDetail($order);
            if($this->customerId){
                /**
                 * check and save info for membership type product order
                 */
                $this->checkForMembershipProductOrder($order,$quote);
                /**
                 * check for applied gift card order
                 */
                $this->checkForAppliedGiftCardOrder($order,$quote,$this->customerId);
                /**
                 * check for applied reward points
                 */
                $this->checkForAppliedRewardPointOrder($order);
            }

            $this->returnArray["orderId"] = $order->getId();
            $this->returnArray["success"] = true;
            $this->returnArray["incrementId"] = $order->getIncrementId();
            $this->emulate->stopEnvironmentEmulation($environment);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = $e->getMessage();
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }
    public function checkForAppliedRewardPointOrder($order){
        if ($order && $order->getId()) {
            $purchase = $this->rewardsPurchase->getByQuote($order->getQuoteId());
            if (!$purchase) {
                return;
            }
//            $this->refreshPoints($purchase->getQuote(), true, true);
            if (!$this->orderService->isLocked($order)) {
                $this->orderService->lock($order);
                if ($order->getCustomerId()) {
                    $result=$this->deductSpentPoints->createTransaction($order);
                    if ($result) {
                        $this->returnArray["reward_message"] = __(
                            '%1 reward points were spend on this order.', $result
                        );
                    }
                }
            }
        }
    }
    public function checkForMembershipProductOrder($order, $quote){
        if($order->getId()){
            $order = $this->_order->load($order->getId());
            $currentOrder = $order->getData();
            $itemCollection = $order->getItemsCollection();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();

            foreach ($itemCollection as $item) {
                
                if (isset($currentOrder['status']) && (!empty($item)) && ($item->getProductType() == "Membership")) {
                    $options = $item->getProductOptions();
                    $productId = $item->getProductId();
                    
                    $itemss=$quote->getAllItems();
                    foreach($itemss as $itemee){
                        $customOptions = $itemee->getOptionByCode('additional_options');
                        $row = $customOptions->getData();
                        $newrow=[];
                        $newrow=$row['value'];
                        $roww=json_decode($newrow,TRUE);
                        $durationlabel=$roww['duration']['label'];
                        $durationvalue=$roww['duration']['value'];
                        $durationunitlabel=$roww['duration_unit']['label'];
                        $durationunitvalue=$roww['duration_unit']['value'];
                        $durationpricelabel=$roww['duration_price']['label'];
                        $durationpricevalue=$roww['duration_price']['value'];
                    }
                    

                    // $product_options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                    // $customer_plan = unserialize($product_options['info_buyRequest']['duration_option']);
                    // $duration = $customer_plan['duration'];
                    // $durationUnit = $customer_plan['duration_unit'];
                    
                    $orderStatus = $order->getStatus();
                    if ($productId) 
                    {
                        $membershipProduct = $this->getMembershipProduct($productId);
                        
                        // $product_options = json_decode($options,TRUE);
                        $product_options = $options;
                        
                        //$customer_plan = $product_options['info_buyRequest']['duration_option'];
                        $customer_plan=[];
                        $customer_plan = array(
                            'duration' => $durationvalue,
                            'duration_unit' => $durationunitvalue,
                            'price' => $durationpricevalue
                        );
                        $customer_plan = serialize($customer_plan);
                         /*print_r($options);
                        exit();*/
                        
                        $payment = $order->getPayment();

                        //$customerPlanArray = unserialize($customer_plan);
                        //$duration = $customerPlanArray['duration'];
                        $duration = $durationvalue;
                        //$durationUnit = $customerPlanArray['duration_unit'];
                        $durationUnit = $durationunitvalue;
                        
                        $shortDate = "+".$duration." ".$durationUnit;
                        $increaseDate = strtotime($shortDate);
                        $planExpiryDate = date("Y-m-d h:i:s", $increaseDate);

                        
                        $data = ['membership_product_id'=>$membershipProduct['membership_product_id'],
                                'product_id'=>$productId,
                                'order_id'=>$item->getOrderId(),
                                'order_status'=>$currentOrder['status'],
                                'customer_id'=>$currentOrder['customer_id'],
                                'customer_email'=>$currentOrder['customer_email'],
                                'customer_plan'=>$customer_plan,
                                'price'=>$item->getPrice(),
                                // 'price'=>$order->getGrandTotal(),
                                'related_customer_group_id'=>$membershipProduct['related_customer_group_id'],
                                'plan_expiry_date'=>$planExpiryDate];

                        if($duration > 1 && $payment->getMethod() == 'facpayment'){
                            
                            $tableName = $resource->getTableName('recurring_order');
                            // print_r(unserialize($customer_plan));
                            $planperiod = unserialize($customer_plan);
                            $today = strtotime("now");
                            
                             $start_week = date("d",$today);
                            $recurringdata = array();
                            for ($i = 1; $i < $planperiod['duration']; $i++) 
                            {
                                $order_id = $item->getOrderId();
                                $customer_id = $currentOrder['customer_id'];
                                $increment_id = $currentOrder['increment_id'];
                                $customer_email = $currentOrder['customer_email'];
                                $date = date("Y-m-d", strtotime( date( 'Y-m-'.$start_week )." +$i months"));
                                $price = $item->getPrice();
                                // $recurringdata []=  $months;
                                $sql = "INSERT INTO " . $tableName . "(order_id,increment_id,customer_id,customer_email,next_date,price) VALUES (".$order_id.",'".$increment_id."',".$customer_id.",'".$customer_email."','".$date."',".$price.")";
                                // echo $sql."<br>";
                                $connection->query($sql);
                            }
                        }
                        
                        if (count($data)>0) {
                            $model = $this->_MembershipOrdersFactory->create();
                            $model->addData($data);
                            $model->save();

                            $customer_id = $this->customerId;
                            $customer = $this->customerRepository->getById($customer_id);
                            $group_id = $membershipProduct['customer_group_id'];
                            $customer->setGroupId($group_id);
                            $this->customerRepository->save($customer);

                        }
                    }
                }
            }
        }
    }
    public function checkForAppliedGiftCardOrder($order, $quote, $customerid){
        // $oids=$order->getId();
        // $sl = $this->_salesOrder->load($oids);
        //$quote = $this->quoteFactory->create()->load($sl->getQuoteId());
        if (!empty($quote->getGiftCode())) {
            $gift_user_data=[];
            
            $customer = $this->customerRepository->getById($customerid);
            $fName = $customer->getFirstname();
            $lName = $customer->getLastname();
            $customerName = $fName." ".$lName;
            $customerEmail= $customer->getEmail();
            //$customerName=$this->_customerSession->getCustomer()->getName();
            $gift_user_data["orderId"]=$order->getIncrementId();
            $gift_user_data["reciever_email"]=$customerEmail;
            $gift_user_data["reciever_name"]=$customerName;
            $gift_user_data["reduced_ammount"]=$quote->getBasefee();
            $model3=$this->_giftUserFactory->create()
            ->getCollection()
            ->addFieldToFilter("code", $quote->getGiftCode());
            foreach ($model3 as $m3) {
                $amnt=$m3->getRemainingAmt();
                $m3->setRemainingAmt($amnt+$quote->getBasefee())->save();
            }
            $invoiceId = '';
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoiceId = $invoice->getId();
            }
            if (!empty($invoiceId)) {
                $order->setFee($quote->getBasefee());
                $order->save();
                $invoiceData = $this->invoiceRepository->get($invoiceId);
                $invoiceData->setFee($quote->getBasefee());
                $invoiceData->setGrandTotal($invoiceData->getGrandTotal() + $quote->getFee());
                $invoiceData->setBaseGrandTotal($invoiceData->getBaseGrandTotal() + $quote->getBasefee());
                $invoiceData->save();
            }
            $quote->setGift(false);
            $quote->setAmount('');
            $quote->setGiftCode('');
            $quote->save();
            $this->checkoutSession->setGift(false);
            $this->checkoutSession->setAmount('');
            $this->checkoutSession->setGiftCode('');
        }
    } 
    public function getMembershipProduct($productId)
    {
        $model = $this->_MembershipProductsFactory->create();
        $model->load($productId, 'product_id');
        return $model->getData();
    }

    /**
     * Function to prepae quote in case of guest checkout
     *
     * @param Magento\Quote\Model\Quote $quote quote
     *
     * @return Magento\Quote\Model\Quote
     */
    public function prepareGuestQuote($quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
        return $quote;
    }

    /**
     * Function to add OrderDetails to the return Array for create account feature
     *
     * @param \Magento\Sale\Model\Order $order order
     *
     * @return void
     */
    public function getCustomerDetails($order)
    {
        $this->returnArray['customerDetails']['guestCustomer'] = $order->getCustomerIsGuest();
        $this->returnArray['customerDetails']['groupId'] = $order->getCustomerGroupId();
        $this->returnArray['customerDetails']['firstname'] = $order->getCustomerFirstname();
        $this->returnArray['customerDetails']['email'] = $order->getCustomerEmail();
        $this->returnArray['customerDetails']['lastname'] = $order->getCustomerMiddlename();
        $this->returnArray['customerDetails']['lastname'] = $order->getCustomerLastname();
    }

    public function prepareNewCustomerQuote($quote)
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $customer = $quote->getCustomer();
        $customerBillingData = $billing->exportCustomerAddress();
        $dataArray = $this->objectCopyService->getDataFromFieldset("checkout_onepage_quote", "to_customer", $quote);
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $dataArray,
            "\Magento\Customer\Api\Data\CustomerInterface::class"
        );
        $quote->setCustomer($customer)->setCustomerId(true);
        $customerBillingData->setIsDefaultBilling(true);
        if ($shipping) {
            if (!$shipping->getSameAsBilling()) {
                $customerShippingData = $shipping->exportCustomerAddress();
                $customerShippingData->setIsDefaultShipping(true);
                $shipping->setCustomerAddressData($customerShippingData);
                // Add shipping address to quote since customer Data Object does not hold address information ////
                $quote->addCustomerAddress($customerShippingData);
            } else {
                $shipping->setCustomerAddressData($customerBillingData);
                $customerBillingData->setIsDefaultShipping(true);
            }
        } else {
            $customerBillingData->setIsDefaultShipping(true);
        }
        $billing->setCustomerAddressData($customerBillingData);
        $quote->addCustomerAddress($customerBillingData);
        return $quote;
    }

    public function prepareCustomerQuote($quote, $customerId)
    {
        $quoteBilling = $quote->getBillingAddress();
        $quoteShipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $customer = $this->customerRepository->getById($customerId);
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();
        if ($quoteShipping
            && !$quoteShipping->getSameAsBilling()
            && (!$quoteShipping->getCustomerId()
            || $quoteShipping->getSaveInAddressBook())
        ) {
            $shippingAddress = $quoteShipping->exportCustomerAddress();
            if (!$hasDefaultShipping) {
                // Make provided address as default shipping address ////////////////
                $shippingAddress->setIsDefaultShipping(true);
                $hasDefaultShipping = true;
            }
            $quote->addCustomerAddress($shippingAddress);
            $quoteShipping->setCustomerAddressData($shippingAddress);
        }
        if (!$quoteBilling->getCustomerId() || $quoteBilling->getSaveInAddressBook()) {
            $billingAddress = $quoteBilling->exportCustomerAddress();
            if (!$hasDefaultBilling) {
                // Make provided address as default shipping address ////////////////
                if (!$hasDefaultShipping) {
                    // Make provided address as default shipping address ////////////
                    $billingAddress->setIsDefaultShipping(true);
                }
                $billingAddress->setIsDefaultBilling(true);
            }
            $quote->addCustomerAddress($billingAddress);
            $quoteBilling->setCustomerAddressData($billingAddress);
        }
        return $quote;
    }

    public function involveNewCustomer($quote)
    {
        $customer = $quote->getCustomer();
        $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
        if ($confirmationStatus === \Magento\Customer\Model\AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED) {
            return ["status"=>false, "message"=>__(
                "You must confirm your account. Please check your email for the confirmation link."
            )];
        } else {
            return ["status"=>true, "message"=>""];
        }
    }

    /**
     * Function to verify request
     *
     * @return void|json
     */
    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->token = $this->wholeData["token"] ?? "";
            $this->method = $this->wholeData["paymentMethod"] ?? 0;
            $this->cc_cid = $this->wholeData["cc_cid"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->cc_type = $this->wholeData["cc_type"] ?? 0;
            $this->cc_number = $this->wholeData["cc_number"] ?? 0;
            $this->cc_exp_year = $this->wholeData["cc_exp_year"] ?? 0;
            $this->billingData = $this->wholeData["billingData"] ?? "{}";
            $this->cc_exp_month = $this->wholeData["cc_exp_month"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->checkoutMethod = $this->wholeData["checkoutMethod"] ?? "guest";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            $this->purchasePoint = $this->wholeData["purchasePoint"] ?? "web";
            if ($this->customerId > 0) {
                $this->checkoutMethod = "customer";
            }
            if (!empty($this->billingData)) {
                $this->billingData = $this->jsonHelper->jsonDecode($this->billingData);
            }
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
     * Set Billing Data in Quote
     *
     * @param Magento\Model\Quote\Model $quote quote
     *
     * @return void
     */
    public function setBillingDataInQuote($quote)
    {
        $result["error"] = false;
        if (!empty($this->billingData)) {
            $saveInAddressBook = 0;
            if (isset($this->billingData["newAddress"]["saveInAddressBook"])) {
                $saveInAddressBook = $this->billingData["newAddress"]["saveInAddressBook"];
            }
            if ($this->checkoutMethod == "register") {
                $saveInAddressBook = 1;
            }
            if (isset($this->billingData["sameAsShipping"]) && $this->billingData["sameAsShipping"] > 0) {
                $sameAsShipping = $this->billingData["sameAsShipping"];
                $this->billingData["addressId"] = $quote->getShippingAddress()->getCustomerAddressId();
            } else {
                $sameAsShipping = 0;
            }
            $addressId = 0;
            if ($this->billingData["addressId"] != "") {
                $addressId = $this->billingData["addressId"];
            }
            $quote->setCheckoutMethod($this->checkoutMethod)->save();
            $newAddress = [];
            if ($this->billingData["newAddress"] != "") {
                if (!empty($this->billingData["newAddress"])) {
                    $newAddress = $this->billingData["newAddress"];
                }
            }
            $address = $quote->getBillingAddress();
            $addressForm = $this->customerForm;
            $addressForm->setFormCode("customer_address_edit")->setEntityType("customer_address");
            if ($addressId > 0) {
                $customerAddress = $this->customerAddress->load($addressId)->getDataModel();
                if ($customerAddress->getId()) {
                    if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
                        $returnArray["message"] = __("Customer Address is not valid.");
                        return $this->getJsonResponse($returnArray);
                    }
                    $address->importCustomerAddressData($customerAddress)->setSaveInAddressBook(0);
                    $addressForm->setEntity($address);
                    $addressErrors = $addressForm->validateData($address->getData());
                    if ($addressErrors !== true) {
                        $returnArray["message"] = implode(", ", $addressErrors);
                        return $this->getJsonResponse($returnArray);
                    }
                }
            } else {
                $addressForm->setEntity($address);
                $addressData = [
                    "fax" => $newAddress["fax"] ?? "",
                    "city" => $newAddress["city"] ?? "",
                    "region" => $newAddress["region"],
                    "prefix" => $newAddress["prefix"] ?? "",
                    "suffix" => $newAddress["suffix"] ?? "",
                    "street" => $newAddress["street"],
                    "company" => $newAddress["company"],
                    "lastname" => $newAddress["lastName"],
                    "postcode" => $newAddress["postcode"],
                    "firstname" => $newAddress["firstName"],
                    "region_id" => $newAddress["region_id"],
                    "telephone" => $newAddress["telephone"],
                    "middlename" => $newAddress["middleName"] ?? "",
                    "country_id" => $newAddress["country_id"],
                ];
                $addressErrors = $addressForm->validateData($addressData);
                if ($addressErrors !== true) {
                    $returnArray["message"] = implode(", ", $addressErrors);
                    return $this->getJsonResponse($returnArray);
                }
                $addressForm->compactData($addressData);
                $address->setCustomerAddressId(null);
                $address->setSaveInAddressBook($saveInAddressBook);
                $quote->setCustomerFirstname($newAddress["firstName"])->setCustomerLastname($newAddress["lastName"]);
            }
            if (in_array($this->checkoutMethod, ["register", "guest"])) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                if (!empty($newAddress["email"]) && !\Zend_Validate::is($newAddress["email"], "EmailAddress")) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Invalid email format")
                    );
                }
                $quote->setCustomerEmail(trim($newAddress["email"]));
                $address->setEmail(trim($newAddress["email"]));
            }
            if (!$address->getEmail() && $quote->getCustomerEmail()) {
                $address->setEmail($quote->getCustomerEmail());
            }
            if (($validateRes = $address->validate()) !== true) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    implode(",", $validateRes)
                );
            }
            if (true !== ($result = $this->_validateCustomerData($this->wholeData))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(implode(",", $result))
                );
            }
            if (!$quote->getCustomerId() && "register" == $quote->getCheckoutMethod()) {
                if ($this->_customerEmailExists($address->getEmail(), $this->storeManager->getStore()->getWebsiteId())
                ) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("This email already exist.")
                    );
                }
            }
            if (!$quote->isVirtual()) {
                $usingCase = isset($sameAsShipping) ? (int)$sameAsShipping : 0;
                switch ($usingCase) {
                    case 0:
                        $shipping = $quote->getShippingAddress();
                        $shipping->setSameAsBilling(0);
                        $setStepDataShipping = 0;
                        break;
                    case 1:
                        $billing = clone $address;
                        $billing->unsAddressId()->unsAddressType();
                        $shipping = $quote->getShippingAddress();
                        $shippingMethod = $shipping->getShippingMethod();
                        $shipping->addData($billing->getData())
                        ->setSameAsBilling(1)
                        ->setSaveInAddressBook(0)
                        ->setShippingMethod($shippingMethod)
                        ->setCollectShippingRates(true);
                        $setStepDataShipping = 1;
                        break;
                }
            }
            $quote->collectTotals()->save();
            if (!$quote->isVirtual() && $setStepDataShipping) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
            }
        } else {
            $result["error"] = true;
            $result["message"] = __("Invalid Billing Data");
            return $result;
        }
    }

    /**
     * Function to save payment Info
     *
     * @param \Magento\Quote\Model\Quote $quote quote
     *
     * @return \Magento\Quote\Model\Quote $quote quote
     */
    public function savePaymentInfo($quote)
    {
        //saving payment //////////////////////////////////////////////////////////////////
        if ($this->method != "") {
            $paymentData = [];
            $paymentInfo["paymentMethod"] = [];
            $paymentData["method"] = $this->method;
            // $paymentInfo["paymentMethod"]["method"] = $this->method;
            // $paymentInfo["paymentMethod"]['additional_data'] = [];
            if ($this->cc_cid != "") {
                $paymentData["cc_cid"] = $this->cc_cid;
                // $paymentInfo["paymentMethod"]['additional_data']["cc_cid"] = $this->cc_cid;
            }
            if ($this->cc_exp_month != "") {
                $paymentData["cc_exp_month"] = $this->cc_exp_month;
                // $paymentInfo["paymentMethod"]['additional_data']["cc_exp_month"] = $this->cc_exp_month;
            }
            if ($this->cc_exp_year != "") {
                $paymentData["cc_exp_year"] = $this->cc_exp_year;
                // $paymentInfo["paymentMethod"]['additional_data']["cc_exp_year"] = $this->cc_exp_year;
            }
            if ($this->cc_number != "") {
                $paymentData["cc_number"] = $this->cc_number;
                // $paymentInfo["paymentMethod"]['additional_data']["cc_number"] = $this->cc_number;
            }
            if ($this->cc_type != "") {
                $paymentData["cc_type"] = $this->cc_type;
                // $paymentInfo["paymentMethod"]['additional_data']["cc_type"] = $this->cc_type;
            }
            if ($quote->isVirtual()) {
                $quote->getBillingAddress()->setPaymentMethod(isset($this->method) ? $this->method : null);
            } else {
                $quote->getShippingAddress()->setPaymentMethod(isset($this->method) ? $this->method : null);
            }
            if (!$quote->isVirtual() && $quote->getShippingAddress()) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
            }
            try {
                $paymentData[""] = [\Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL];
                // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                // $wepApirequest = $objectManager->create(\Magento\Framework\Webapi\Rest\Request::class);
                
                // $wepApirequest->setParams($paymentInfo);
                // echo "<pre>";
                // print_r($wepApirequest->getRequestData());
                // die();
                $payment = $quote->getPayment()->importData($paymentData);
                $quote->save();
            } catch (\Exception $e) {
                $this->returnArray["message"] = $e->getMessage(). "";
                $this->helper->printLog($this->returnArray);
                return $this->getJsonResponse($this->returnArray);
            }
        }
    }

    /**
     * Function to save Mobikul Order
     *
     * @param Magento\Sales\Mode\Order $order object of order
     *
     * @return void;
     */
    public function saveMobikulOrder($order)
    {
        $customerName  = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
        $this->mobikulOrder->create()
            ->setOrderId($order->getId())
            ->setRealOrderId($order->getRealOrderId())
            ->setStoreId($order->getStoreId())
            ->setCustomerId($order->getCustomerId())
            ->setCustomerName($customerName)
            ->setOrderTotal($order->getGrandTotal())
            ->setCreatedAt($order->getCreatedAt())
            ->save();
    }

    /**
     * Function to save purchase point detail of Order
     *
     * @param Magento\Sales\Mode\Order $order object of order
     *
     * @return void;
     */
    public function savePurchasePointDetail($order)
    {
        $purchasePoint = $this->orderPurchaseFactory->create();
        $purchasePoint->setIncrementId($order->getIncrementId());
        $purchasePoint->setOrderId($order->getEntityId());
        $purchasePoint->setPurchasePoint($this->purchasePoint);
        $purchasePoint->save();
    }
}
