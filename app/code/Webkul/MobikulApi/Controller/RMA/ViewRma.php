<?php
namespace Webkul\MobikulApi\Controller\RMA;
class ViewRma extends AbstractRma
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
            $rma=$this->getRmaDetail();
            if(!$rma){
                $this->returnArray["message"]= "Invalid RMA.";
                return $this->getJsonResponse($this->returnArray);
            }
            $rma_id=$rma->getRmaId();
            $order = $this->getOrder($rma->getOrderId());
            
            $packagecondition="";
            if (!$rma->getResolutionType()==3){
                if ($rma->getPackageCondition() == 0) {
                    $packagecondition="Open";
                }
                else{
                    $packagecondition="Packed";
                }

            }
            // if(!empty($packagecondition)){
            //     $this->returnArray["data"]["rmaData"]=[
            //         "packagecondition" => $packagecondition
            //     ];
            // }
            $createdon=date("Y-m-d", $this->getTimestamp($rma->getCreatedAt()));
            $adminconsignmentno="";
            if ($rma->getResolutionType() == 1 && $rma->getAdminConsignmentNo() != "") {
                $adminconsignmentno=$rma->getAdminConsignmentNo();
            }
            $yourconsignmentno="";
            if ($rma->getResolutionType() != 3 && $rma->getStatus() != 2 && $rma->getStatus() != 4){
                $yourconsignmentno=$rma->getCustomerConsignmentNo();
            }
            $status=$rma->getStatus();
			//$finalstatus=$rma->getFinalStatus();
            $rmaStatus=$this->RmaHelper->getRmaStatusTitle($status);
            $productdeliveryStatus=$this->RmaHelper->getRmaOrderStatusTitle($rma->getCustomerDeliveryStatus());
            if ($rma->getResolutionType() == 0) {
                $resolutiontype="Refund";
            }elseif ($rma->getResolutionType() == 1){
                $resolutiontype="Exchange";
            }else {
                $resolutiontype="Cancel Item";
            }
            $additionalinfo=$rma->getAdditionalInfo();

            $fieldData = $this->RmaHelper->getFieldData($rma_id);
            $additionalfieldData=[];
            foreach ($fieldData as $field) {
                $fieldlable=$field->getLabel();
                if (($field->getInputType()=='checkbox') || ($field->getInputType()=='multiselect')) {
                    $check = false;
                    $value="";
                    $vl = explode(",", $field->getValue());
                    $op = $field->getSelectOption();
                    $op = explode(",", $op);
                    foreach ($op as $key) {
                        $val = explode('=>', $key);
                        if (in_array($val[0], $vl)) {
                            if (!$check) {
                                $value= $val[1];
                            } else { $value= $value.', '.$val[1];
                        }
                        $check = true;
                    }
                    $fieldvalue=$value;
                } //die;
                }elseif (($field->getInputType()=='radio') || ($field->getInputType()=='select')) {
                    $value="";
                    $vl = $field->getValue();
                    $op = $field->getSelectOption();
                    $op = explode(",", $op);
                    foreach ($op as $key) {
                        $val = explode('=>', $key);
                        if ($val[0]==$vl) {
                            $value= $val[1];
                        }
                    } //die;
                    $fieldvalue=$value;
                }else {
                    $fieldvalue= $field->getValue();
                }
                $eachfield=[
                    "label" => $fieldlable,
                    "value" => $fieldvalue
                ];
                $additionalfieldData[]=$eachfield;
            }
            $this->returnArray["data"]["rmaData"]=[
                "order_id" => "#".$rma->getIncrementId(),
                "package_condition" => $packagecondition,
                "created_on" => $createdon,
                "admin_consignment_no" => $adminconsignmentno,
                "your_consignment_no" => $yourconsignmentno,
                "status" => $rmaStatus,
                "product_delivery_status" => $productdeliveryStatus,
                "resolution_type" => $resolutiontype,
                "additional_info" => $additionalinfo
            ];
            $this->returnArray["data"]["rmaData"]["customfields"]=$additionalfieldData;
            /* Get RMA item information starts */
            $orderItems = $this->getItemCollection($rma_id);
            $download_count=count($orderItems);
            $download=0;
            $rmaItemsData=[];
            foreach ($orderItems as $item) {
                $mageItem = $this->getSalesOrderItemDetail($item->getItemId());
                $product = $this->getProductDetail($mageItem->getProductId());
                if ($product) {
                    $image_url = $this->imageHelperObj()->init($product, 'product_page_image_small')
                                ->setImageFile($product->getFile())
                                ->getUrl();
                }
                $reason = $this->getReason($item->getReasonId())->getReason();
                if (!$reason) {
                    $reason = $item->getRmaReason();
                }
                $result=[
                    "name"=> $mageItem->getName(),
                    "image_url"=> $image_url,
                    "sku"=> $mageItem->getSku(),
                    "returned_qty"=> $item->getQty(),
                    "reason"=> $reason,
                    "price" => strip_tags($order->formatPrice($mageItem->getPrice()))
                ];
                $rmaItemsData[]=$result;
            }
            $this->returnArray["data"]["rmaItems"]=$rmaItemsData;
            
           
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    public function getItemCollection($rma_id)
    {
        $collection = $this->_itemCollectionFactory->create()
          ->addFieldToFilter('rma_id', $rma_id);

        return $collection;
    }
    public function getSalesOrderItemDetail($itemId)
    {
        return $this->orderItemRepository->get($itemId);
    }
    public function getProductDetail($productId)
    {
        try {
            return $this->productRepositoryInt->getById($productId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }
    public function getReason($reasonId)
    {
        return $this->reasonRepository->getById($reasonId);
    }
    public function getTimestamp($date){
        return $date = $this->_date->timestamp($date);
    }
    public function getOrder($orderId){
        return $this->orderRepository->get($orderId);
    }
    public function getRmaDetail()
    {
        $id = $this->rmaId;

        $collection = $this->detailsCollection->create()
            ->addFieldToFilter('customer_id', ['eq' => $this->customerId])
            ->addFieldToFilter('rma_id', ['eq' => $id]);

        if ($collection->getSize()) {
            foreach ($collection as $value) {
                return $value;
            }
        } else {
            return false;
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

