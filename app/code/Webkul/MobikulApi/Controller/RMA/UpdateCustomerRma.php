<?php
namespace Webkul\MobikulApi\Controller\RMA;
class UpdateCustomerRma extends AbstractRma
{
    public function execute()
    {
        $this->verifyRequest();
        $post=$this->wholeData;
        $post["rma_id"]=$this->rmaId;
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
            $collection = $this->detailsCollection->create()->addFieldToFilter('customer_id', $this->customerId);
		    $size=$collection->getSize();
		    if($size == 0){
                $this->returnArray["message"]="No RMA request found.";
			    return $this->getJsonResponse($this->returnArray);
		    }
            else{
                $statusFlag = false;
                $deliveryFlag = false;
                $error = false;
                $model = $this->rmaRepository->getById($this->rmaId);
                $this->coversationmessage = preg_replace('/<[^>]*>/', '', $this->coversationmessage);
                if (trim($this->coversationmessage) != '') {
                    $conversationModel = $this->conversationDataFactory->create()
                    ->setRmaId($this->rmaId)
                    ->setMessage($this->coversationmessage)
                    ->setCreatedAt(time())
                    ->setSender('customer');
                    try {
                        $this->conversationRepository->save($conversationModel);
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->returnArray["message"]=$e->getMessage();
                        
                    } catch (\Exception $e) {
                        $this->returnArray["message"]="Something went wrong while saving the Message.";
                        
                    }
                }
                $lastRmaId = $model->getId();
                 if (isset($this->wholeData["solved"])) {
                     $model->setStatus(2);
                     $model->setFinalStatus(4);
                     $model->setAdminStatus(6);
                     $statusFlag = true;
                     $message = '<span>'.__("RMA Status Updated.").'</span><br/><br/><p class="msg-content">'.
                       __('RMA status has been changed to Solved.').'</p>';
                     $this->saveRmaHistory($lastRmaId, $message);
                 }
                 if (isset($this->wholeData["pending"])) {
                     $model->setStatus(0);
                     $model->setFinalStatus(0);
                     $model->setAdminStatus(0);
                     $message = '<span>'.__("RMA Status Updated.").'</span><br/><br/><p class="msg-content">'.
                       __('RMA status has been changed to Pending.').'</p>';
                     $this->saveRmaHistory($lastRmaId, $message);
                     $statusFlag = true;
                 }
                 if ($model->getCustomerConsignmentNo() != $this->customerconsignmentno) {
                     $model->setCustomerConsignmentNo($this->customerconsignmentno);
                     $deliveryFlag = true;
                 }
                try {
                    $lastRmaId = $this->rmaRepository->save($model)->getId();
                } catch (\Exception $e) {
                    $this->returnArray["message"]="Something went wrong while updating the RMA.";
                    return $this->getJsonResponse($this->returnArray);
                }
                $selfEmail = [
                    'check' => false,
                    'area' => 'frontend'
                ];
                if (isset($this->wholeData["receive_email"])) {
                    $selfEmail['check'] = true;
                }
                $fileName = '';
                if ($statusFlag == true || $deliveryFlag == true) {
                    $this->_emailHelper->updateRmaEmail($post, $model, $statusFlag, $deliveryFlag, $selfEmail, $fileName, $this->customerId);
                } else {
                    $this->_emailHelper->newMessageEmail($post, $model, $selfEmail, $fileName, $this->customerId);
                }
                $this->returnArray["message"]="RMA Successfully Updated.";
                
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
    public function saveRmaHistory($rmaId, $message)
    {
        $conversationModel = $this->conversationDataFactory->create()
          ->setRmaId($rmaId)
          ->setMessage($message)
          ->setCreatedAt(time())
          ->setSender('default');
        try {
            $this->conversationRepository->save($conversationModel);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"]=$e->getMessage();
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"]="Something went wrong while saving the Message";
            return $this->getJsonResponse($this->returnArray);
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
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->rmaId = $this->wholeData["rmaId"] ?? "";
            $this->coversationmessage = $this->wholeData["message"] ?? "";
            //$this->solved = $this->wholeData["solved"] ?? 0;
            //$this->pending = $this->wholeData["pending"] ?? 0;
            $this->customerconsignmentno = $this->wholeData["customer_consignment_no"] ?? "";
            //$this->receiveemail = $this->wholeData["receive_email"] ?? 0;
            
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

