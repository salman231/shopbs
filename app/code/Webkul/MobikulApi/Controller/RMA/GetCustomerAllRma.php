<?php
namespace Webkul\MobikulApi\Controller\RMA;
class GetCustomerAllRma extends AbstractRma
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
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            $this->returnArray["success"]= true;
            $this->returnArray["message"]= "";
            $collection = $this->detailsCollection->create()->addFieldToFilter('customer_id', $this->customerId);
		    $size=$collection->getSize();
		    if($size == 0){
                $this->returnArray["message"]="No RMA request found.";
			    return $this->getJsonResponse($this->returnArray);
		    }
            else{
                //$rma = $collection;
			    //$rma=$this->mpRmaHelper->applyFilter($rma);        	
			    //$sortingOrder = $this->mpRmaHelper->getSortingOrder();
        		//$sortingField = $this->mpRmaHelper->getSortingField();
        		//$rma->setOrder($sortingField, $sortingOrder);
			    //$collection=$rma;
                $rmaHistoryData=[];
                foreach($collection as $item){
					$status=$item->getStatus();
					$finalstatus=$item->getFinalStatus();
					$statustitle=$this->RmaHelper->getRmaStatusTitle($status, $finalstatus);
                    $rmadate= date("Y-m-d", $this->getTimestamp($item->getCreatedAt()));
                    $result=[
						"id"=>$item->getId(),
						"order_id"=>$item->getIncrementId(),
						"status"=>$statustitle,
						"date"=>$rmadate
                    ];
                    $rmaHistoryData[]=$result;
				}
                $this->returnArray["data"]["rmahistory"]=$rmaHistoryData;
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

