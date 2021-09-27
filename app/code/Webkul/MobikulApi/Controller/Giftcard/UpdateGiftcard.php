<?php
namespace Webkul\MobikulApi\Controller\Giftcard;

class UpdateGiftcard extends AbstractGiftcard
{
    public function execute()
    {
        $this->verifyRequest();
        $quote = new \Magento\Framework\DataObject();
        if ($this->customerId != 0) {
            $quote = $this->helper->getCustomerQuote($this->customerId);
        }
        if ($this->quoteId != 0) {
            $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
        }
        // Checking customer token //////////////////////////////////////////////
        if (!$this->customerId && $this->customerToken != "") {
            $this->returnArray["otherError"] = "customerNotExist";
            throw new \Magento\Framework\Exception\LocalizedException(
                __("As customer you are requesting does not exist, so you need to logout.")
            );
        }
    	if ($this->customerId) {
            //$this->returnArray = [];
            
            $this->returnArray["success"]= true;
            $this->returnArray["message"]= "";
            $param=$this->wholeData;
            $setMessages = false;
            if (isset($param['cartpage']) && $param['cartpage']) {
                $setMessages = true;
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            //$cart = $objectManager->get(\Magento\Checkout\Model\Cart::class);
            $grandTotal = 0;
            $itemCollection = $this->itemCollectionFactory->create()->setQuote($quote);
            foreach ($itemCollection as $item) {
          
                if ($item->getProductType() != "giftcard") {
                    $grandTotal += $item->getPrice() * $item->getQty();
                }
            }
            if ($grandTotal == 0) {
                $this->returnArray["message"] ="Invalid Request";
                return $this->getJsonResponse($this->returnArray);
            }
            // if ($grandTotal > 0) {
            //     $this->returnArray["message"] ="Items Found";
            //     $this->returnArray["totals"] =$grandTotal;
            //     return $this->getJsonResponse($this->returnArray);
            // }
            if (empty($param['amount'])) {
                $this->returnArray["message"] ="Amount must be filled.";
                return $this->getJsonResponse($this->returnArray);
            }
            //$param["code"] = urldecode($param["code"]);
            $rates = $this->_dataHelper->getCurrentCurrencyRate();
            $price = ($grandTotal < $param['amount']) ? $grandTotal : $param['amount'];
            $price=$price/$rates;
            $param['amount']=$price;

            if ((real)$param['amount']>0) {
                $whom="";
                $collections=$this->_giftuser->create()->getCollection();
                $model = $collections->addFieldToFilter("code", $param["code"]);
                if ($model->getSize()) {
                    $collectionData = $model->getData();
                    
                    $remainingamount = $model->getFirstItem()->getRemainingAmt();
                    if ($param['amount'] > $remainingamount) {
                        $remainingamount = $this->_dataHelper->getCurrentCurrencySymbol().$remainingamount;
                        $this->returnArray["message"]=__("You have only %1 amount remaining in your gift card", $remainingamount); 
                        return $this->getJsonResponse($this->returnArray);
                    }
                    $customer = $this->_customerRepositoryInterface->getById($this->customerId);
                    $customerEmail=$customer->getEmail();
                    $userCheck =  $this->userCheck($collectionData, $customerEmail);
                    if ($userCheck == 'error') {
                            $this->returnArray["message"]=__('Invalid user'); 
                            return $this->getJsonResponse($this->returnArray);
    
                    }
                    $giftDetailModel = $this->_giftDetail->create()->load($model->getColumnValues('giftcodeid')[0]);
                    $duration = $giftDetailModel->getDuration();
                    $websiteIds = $giftDetailModel->getWebsiteIds();
                    if ($websiteIds) {
                        $manager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
                        $currentWebsiteId= $manager->getStore($this->storeId)->getWebsiteId();
                        //$currentWebsiteId = $manager->getWebsiteId();
                        if (!in_array($currentWebsiteId, explode(',', $websiteIds))) {
                            $this->returnArray["message"] = __("The gift code %1 is not available", $param['code']);
                            return $this->getJsonResponse($this->returnArray);
                        }
                    }
                    $isExpire = $this->_dataHelper->checkExpirationOfGiftCard(
                        $model->getColumnValues('alloted')[0],
                        $duration
                    );
                    $codeCheck = $this->codeCheck($isExpire, $model, $param);
                    if ($codeCheck == 'apply') {
                        $this->returnArray["message"] = __("The gift code %1 is expired", $param['code']);
                        return $this->getJsonResponse($this->returnArray);
                    }
    
                    if ($codeCheck == 'disable') {
                       
                        $this->returnArray["message"] =
                        __("The gift code %1 is disable. Please contact administration.", $param['code']);
                        return $this->getJsonResponse($this->returnArray);
                    }
                    foreach ($model as $m) {
                            $whom=$m->getEmail();
                    }
         
                    $codeData = $this->codeApply($customerEmail, $param, $collections);
                    if ($codeData == 'expire') {
                        $this->returnArray["message"] =__("Gift code has been expired.");
                        return $this->getJsonResponse($this->returnArray);
                    }
                    if ($codeData == 'expire') {
                        $this->returnArray["message"] =__("Please enter a valid amount");
                        return $this->getJsonResponse($this->returnArray);
                    }
                    if ($codeData == 'success') {
                        $this->returnArray["message"] =__('Gift Card Discount Applied Successfully');
                        return $this->getJsonResponse($this->returnArray);
                    }
                    if ($codeData == 'valid') {
                        $this->returnArray["message"] = __("The gift code %1 is not valid", $param['code']);
                        return $this->getJsonResponse($this->returnArray);
                    }
                    if ($codeData == 'required') {
                        $this->returnArray["message"] =__("code is required");
                        return $this->getJsonResponse($this->returnArray);
                    }
                } else {
                    $this->returnArray["message"] =__("The gift code %1 is not valid", $param['code']);
                    return $this->getJsonResponse($this->returnArray);
                }
            } else {
                $collection = $this->_salesRule->getCollection()->load();
                foreach ($collection as $mo) {
                    // Delete coupon
                    if ($mo->getName() == $param['code']) {
                        $mo->delete();
                        //$this->_backendSession->setCoupancode(null);
                        //$this->_backendSession->setReduceprice(null);
                    }
                }
                $this->returnArray["message"] =__("Please enter a valid amount");
                return $this->getJsonResponse($this->returnArray);
            }

            
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }

    public function userCheck($collectionData, $customerEmail)
    {
        if ($collectionData[0]['email'] != $customerEmail) {
            $data = 'error';
            return $data;
        }
    }

    public function codeCheck($isExpire, $model, $param)
    {
        if ($isExpire) {
            foreach ($model as $giftUserModel) {
                $giftUserModel->setIsExpire(1);
                $giftUserModel->save();
            }
            return 'apply';
           
        }
        if ($model->getColumnValues('is_active')[0] != "yes") {
            return 'disable';
           
        }
    }
 
    public function codeApply($customerEmail, $param, $collections)
    {
        $usermodel=$collections->addFieldToFilter("email", $customerEmail)->addFieldToFilter(
            "code",
            $param['code']
        );
        $acamm=0;
        if ($usermodel->getSize() > 0) {
            foreach ($usermodel as $u) {
                $acamm=(real)$u->getRemainingAmt();
            }
            if ((real)$param['amount']>$acamm) {
                $param['amount']=$acamm;
            }
        }
        if ((real)$param['amount']==0) {
            $collection = $this->_salesRule->getCollection();
            foreach ($collection as $mo) {
                if ($mo->getName() == $param['code']) {
                    $mo->delete();
                    //$this->_backendSession->setCoupancode(null);
                    //$this->_backendSession->setReduceprice(null);
                }
            }
            return 'expire';
          
        } elseif ((real)$param['amount']<=$acamm) {
            $saveQuote =  $this->codeSaveInQuote($param, $collections);
            return $saveQuote;
        } else {
            //$this->_backendSession->setCoupancode(null);
            //$this->_backendSession->setReduceprice(null);
            return 'valid';
           
        }
    }
    public function codeSaveInQuote($param, $collections)
    {
        if (!empty($param['code'])) {
            $model=$collections->addFieldToFilter("code", $param['code']);
            foreach ($model as $m) {
                $giftcode=$m->getCode();
            }
            if ($giftcode==$param['code']) {
                $this->checkoutSession->setGift(true);
                //$quote->setGift(true);
                $rates = $this->_dataHelper->getCurrentCurrencyRate();
                $dis = (real)$param['amount']*$rates;
                $quote = $this->helper->getCustomerQuote($this->customerId);
                //$quote->setGift(true);
                $quoteDiscount= $quote->getSubtotalWithDiscount();
                $quoteSubtotal= $quote->getSubtotal();

                //$quoteDiscount= $this->_quote->getQuote()->getSubtotalWithDiscount();
                //$quoteSubtotal= $this->_quote->getQuote()->getSubtotal();
                if ($quoteDiscount >= $dis) {
                    $discount = $dis;
                } else {
                    $discount = $quoteDiscount;
                }
                    $quote->setShippingAmount(90)->save();
                    $this->checkoutSession->setQuoteId($quote->getId());
                    $this->checkoutSession->setAmount(-$discount);
                    $this->checkoutSession->setGiftCode($param['code']);
                    // $quote->setAmount(-$discount);
                    // $quote->setGiftCode($param['code']);
                    //$quote->setGiftCardDiscount(-$discount);
                    $quote
                    ->collectTotals()
                    ->save();
                    return 'success';
            } else {
                return 'valid';
            }
        } else {
            return 'required';
           
        }
    }
    
    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->itemId = $this->wholeData["itemId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

