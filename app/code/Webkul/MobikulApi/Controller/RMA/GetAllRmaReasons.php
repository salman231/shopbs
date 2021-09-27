<?php
namespace Webkul\MobikulApi\Controller\RMA;

class GetAllRmaReasons extends AbstractRma
{
    public function execute()
    {
        $this->verifyRequest();
        // Checking customer token //////////////////////////////////////////////
        if (!$this->customerId && $this->customerToken != "") {
            $this->returnArray["otherError"] = "customerNotExist";
            throw new \Magento\Framework\Exception\LocalizedException(
                __("As customer you are requesting does not exist, so you need to logout.")
            );
        }
    	if ($this->customerId) {
            $this->returnArray["success"]= true;
            $this->returnArray["message"]= "";
            $reasons=$this->getReasonCollection();
            $size=$reasons->getSize();
            if($size > 0){
                $regionsdata=[];
                foreach($reasons as $reason){
                    $result=[
						"id"=>$reason->getId(),
						"reason"=>$reason->getReason()
                    ];
                    $regionsdata[]=$result;
                }
                $this->returnArray["data"]["reasons"]=$regionsdata;
            }
            else{
                $this->returnArray["message"]= "No reasons found.";
            }
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    public function getReasonCollection()
    {
        return $this->_reasonCollectionFactory->create()
                    ->addFieldToFilter('status', 1);
    }
    /**
     * Verify Request function to verify Customer and Request
     *
     * @throws Exception customerNotExist
     * @return json | void
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

