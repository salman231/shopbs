<?php
namespace Webkul\MobikulApi\Controller\RMA;

class GetResoDelStatusOptions extends AbstractRma
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
            $order= $this->_order->loadByIncrementId($this->orderId);
            $id=$order->getId();
            $orderr = $this->orderRepository->get($id);
            $invoice_status = 1;
            if ($orderr->hasInvoices()) {
                $invoice_status = 2;
            }
            $resolutionoptions=$this->getResolutionTypes($invoice_status);
            if(count($resolutionoptions)){
                $resolutionoptiondata=[];
                foreach($resolutionoptions as $resolutionoption){
                    $result=[
                        "value"=>$resolutionoption["value"],
                        "label"=>$resolutionoption["label"]
                    ];
                    $resolutionoptiondata[]=$result;
                }
                $this->returnArray["data"]["resolutions"]=$resolutionoptiondata;
            }else{
                $this->returnArray["data"]["resolutions"]="";
            }
            $deliverystatusoptions=$this->getDeliveryStatus();
            if(count($resolutionoptions)){
                $deliverystatusoptiondata=[];
                foreach($deliverystatusoptions as $deliverystatusoption){
                    $result=[
                        "value"=>$deliverystatusoption["value"],
                        "label"=>$deliverystatusoption["label"]
                    ];
                    $deliverystatusoptiondata[]=$result;
                }
                $this->returnArray["data"]["deliverystatus"]=$deliverystatusoptiondata;
            }else{
                $this->returnArray["data"]["deliverystatus"]="";
            }
            $packcondition=[
                ['value' => '0', 'label' =>  __('Open')],
                ['value' => '1', 'label' => __('Packed')]
            ];
            $this->returnArray["data"]["packingcondition"]=$packcondition;
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    public function getResolutionTypes($status = 0)
    {
        if ($status==1) {
            return [
                ['value' => '1', 'label' =>  __('Exchange')],
                ['value' => '3', 'label' => __('Cancel Items')]
            ];
        } elseif ($status == 2) {
            return [
                ['value'=>'0', 'label' =>  __('Refund')],
                ['value' => '1', 'label' =>  __('Exchange')]
            ];
        } else {
            return [
                    ['value'=>'0', 'label' =>  __('Refund')],
                    ['value' => '1', 'label' =>  __('Exchange')],
                    ['value' => '3', 'label' => __('Cancel Items')]
                ];
        }
    }

    public function getDeliveryStatus()
    {
        $resoulution = [
          ['value'=>'0', 'label' =>  __('Not Delivered')],['value' => '1', 'label' =>  __('Delivered')]
        ];
        return $resoulution;
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
            $this->orderId = $this->wholeData["orderId"] ?? 0;
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

