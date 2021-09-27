<?php
namespace Webkul\MobikulApi\Controller\Giftcard;

class ClearDiscount extends AbstractGiftcard
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
            $this->returnArray["success"]=true;
            $this->returnArray["message"]="";
            $quote = $this->helper->getCustomerQuote($this->customerId);
            $this->quoteFactory->create()->load($quote->getId())->setFee(null)->setGiftCode(null)->save();
            //$quote->setFee(null)->setGiftCode(null)->save();
            $this->checkoutSession->setQuoteId($quote->getId());
            $this->checkoutSession->setAmount(null);
            $this->checkoutSession->setGiftCode(null);
            $this->returnArray["message"]="Gift Card Discount Removed.";
            
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
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
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

