<?php
namespace Webkul\MobikulApi\Controller\RMA;
class GetRmaConversation extends AbstractRma
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
            //$this->returnArray = [];
            //$environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->returnArray["success"]= true;
            $this->returnArray["message"]= "";
            $collection = $this->getRmaCollection($this->rmaId);
		    $size=$collection->getSize();
		    if($size == 0){
                $this->returnArray["message"]="No RMA conversation found.";
			    return $this->getJsonResponse($this->returnArray);
		    }
            else{
                $conversationHistoryData=[];
                foreach ($collection as $eachconver) {
                    $sender="";
                    if($eachconver->getSender() == 'customer'){
                        $sender="You.";
                    }
                    else{
                        $sender="Admin.";
                    }
                    $result=[
						"sender"=>$sender,
						"rma_message"=>$eachconver->getMessage()
                    ];
                    $conversationHistoryData[]=$result;
                }
                $this->returnArray["data"]["rmaconversationhistory"]=$conversationHistoryData;
                //$this->returnArray["data"]["rmahistory"]=$rmaHistoryData;
            }
           
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    public function getTimestamp($date){
        return $date = $this->_date->timestamp($date);
    }
    public function getRmaCollection($rmaId)
    {
        if (!$this->customerId) {
            return false;
        }
        $collection = $this->conversationCollectionFactory->create()
              ->addFieldToFilter('rma_id', $rmaId)
              ->setOrder('created_at', 'ASC');
            $this->conversation = $collection;
        
        return $this->conversation;
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
            $this->rmaId = $this->wholeData["rmaId"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

