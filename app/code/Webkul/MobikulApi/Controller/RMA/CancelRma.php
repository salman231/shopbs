<?php
namespace Webkul\MobikulApi\Controller\RMA;
class CancelRma extends AbstractRma
{
    public function execute()
    {
        $this->returnArray["success"]= true;
        $this->verifyRequest();
        //$post=$this->wholeData;
        //$post["rma_id"]=$this->rmaId;
        // Checking customer token //////////////////////////////////////////////
        if (!$this->customerId && $this->customerToken != "") {
            $this->returnArray["otherError"] = "customerNotExist";
            throw new \Magento\Framework\Exception\LocalizedException(
                __("As customer you are requesting does not exist, so you need to logout.")
            );
        }
    	if ($this->customerId) {
            $id=$this->rmaId;
            $model = $this->rmaRepository->getById($id);
            if ($model->getCustomerId() == $this->customerId) {
                $currentrmastatus=$model->getStatus();
                //$this->returnArray["status"]=$currentrmastatus;
                //var_dump($currentrmastatus);
                if($currentrmastatus == "4"){
                    $this->returnArray["message"]="RMA alrready cancelled.";
                    return $this->getJsonResponse($this->returnArray);
                }
                $model->setStatus(4);
                $model->setFinalStatus(1);
                $model->setAdminStatus(0);
                $this->rmaRepository->save($model);

                $message = "RMA Request Cancelled, Your RMA request has been cancelled successfully";
                $this->saveRmaHistory($id, $message);
                $this->_emailHelper->cancelRmaEmail($model);
                $this->returnArray["message"]="RMA with id ".$id." has been cancelled successfully.";
            } else {
                $this->returnArray["message"]="Sorry You Are Not Authorised to cancel this RMA request.";
            }
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
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
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

