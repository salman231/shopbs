<?php
namespace Webkul\MobikulApi\Controller\Membership;

class CancelCustomerMemberShip extends AbstractMembership
{
    public function execute()
    {
        $this->verifyRequest();
        $this->returnArray["success"]=true;
        $postdata=$this->wholeData;
        $id=$postdata['id'];
        
        // Checking customer token //////////////////////////////////////////////
        if (!$this->customerId && $this->customerToken != "") {
            $this->returnArray["otherError"] = "customerNotExist";
            throw new \Magento\Framework\Exception\LocalizedException(
                __("As customer you are requesting does not exist, so you need to logout.")
            );
        }
    	if ($this->customerId) {
            $model = $this->_MembershipOrdersFactory->create()->getCollection();
            $model->addFieldToFilter('customer_id', $this->customerId);
            $model->addFieldToFilter('order_status', 'complete');
            $model->addFieldToFilter('plan_expiry_status', 0);
            $model->addFieldToFilter('order_id', $id);
            
            $data = $model->getData();
           
            if(count($data) == 0){
                $this->returnArray["message"]= "We can't process your request right now. Sorry, that's all we know.";
                return $this->getJsonResponse($this->returnArray);
            }
            foreach ($data as $key => $planvalue) {
                // $planstatus = $planvalue['order_status'];
                if ($planvalue['order_status']!="pending") 
                {
                    if ($planvalue['plan_expiry_status']==0) 
                    {
                        $planStatus = $planvalue['order_status'];
                    
                    } else {
                        $planStatus = $planvalue['order_status'];
                    }
                    // echo $planStatus."<br>";
                }
            }
            try{

            $order = $this->_order->load($id);
            $invoice = $this->_invoice;
            $creditMemoFacory = $this->_creditmemoFactory;
            $creditmemoService = $this->_creditmemoService;
            $incrementId = $order->getIncrementId();

            if($planStatus == 'complete'){
                $invoices = $order->getInvoiceCollection();
                foreach ($invoices as $invoice) {
                    $invoiceincrementid = $invoice->getIncrementId();
                }

                $invoiceobj = $invoice->loadByIncrementId($invoiceincrementid);
                $creditmemo = $creditMemoFacory->createByOrder($order);

                // Don't set invoice if you want to do offline refund
                // $creditmemo->setInvoice($invoiceobj);

                $creditmemoService->refund($creditmemo); 

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('recurring_order');

                $update = "Update " . $tableName . " Set `execute` = 1 WHERE order_id = ".$order->getId()." OR customer_id =".$order->getCustomerId();
                $connection->query($update);

                // echo "CreditMemo Succesfully Created For Order: ".$incrementId;
            }
            if ($order instanceof \Magento\Framework\Model\AbstractModel) {
                if ($order->getState() == 'closed') {
                    foreach ($order->getAllItems() as $item) {
                        if ((count($item)>0) && ($item->getProductType() == "Membership") && ($item->getQtyRefunded()>0)) {
                            $orderId = $item->getOrderId();
                            $Ordersmodel = $this->_MembershipOrdersFactory->create();
                            $Ordersmodel->load($orderId, 'order_id');
                            $membershipOrder = $Ordersmodel->getData();
                            $data = [
                                'order_status'=>$order->getState(),                            
                                'plan_expiry_status'=>1,
                                'plan_expiry_date'=>date("Y-m-d h:i:s")
                            ];
                            $Ordersmodel->addData($data);
                            $Ordersmodel->save();
                            $this->assignCustomerGroup($order->getCustomerId(), $membershipOrder['customer_past_group_id']);
                            $order->setCustomerGroupId($membershipOrder['customer_past_group_id']);                        
                        }
                    }
                }
            }
            if ($order instanceof \Magento\Framework\Model\AbstractModel) {
                if ($order->getState() == 'complete') {
                    foreach ($order->getAllItems() as $item) {
                        if ((count($item)>0) && ($item->getProductType() == "Membership")) {
                            $orderId = $item->getOrderId();
                            $Ordersmodel = $this->_MembershipOrdersFactory->create();
                            $Ordersmodel->load($orderId, 'order_id');
                            $membershipOrder = $Ordersmodel->getData();
                            
                            if (count($membershipOrder)>0) {
                                $customerId = $order->getCustomerId();
                                $orderStatus = $order->getState();
                                $customerPastGroup = $order->getCustomerGroupId();
                                $planExpiryDate = $this->getCustomerPlanExpiryDate($membershipOrder['customer_plan']);
                                
                                $groupId = $membershipOrder['related_customer_group_id'];
                                
                                if ($planExpiryDate) {
                                    $data = ['order_status'=>$orderStatus,'customer_past_group_id'=>$customerPastGroup,'current_customer_group_id'=>$groupId,'plan_expiry_date'=>$planExpiryDate];
                                    $Ordersmodel->addData($data);
                                    $Ordersmodel->save();
                                }
                                $customer = $this->_customerRepository->getById($customerId);
                                $sellercoll = $this->_seller->getCollection();  
                                $sellercoll->addFieldToFilter('customer_id',$customerId)
                                ->addFieldToFilter('status',1);
                                if(count($sellercoll) > 0){
                                    $groupId = 4;
                                }else{
                                    $groupId = 1;
                                }
                                
                                $this->assignCustomerGroup($customerId, $groupId);
                                $order->setCustomerGroupId($groupId);
                            }
                        }
                    }
                }
            }

            $this->returnArray["message"]="Membership Plan has been canceled: ".$incrementId;
            }
            catch(\Exception $e){
                $this->returnArray["message"]=$e->getMessage();
            }
        }
        else{
            $this->returnArray["message"]="Invalid Customer";
        }
        return $this->getJsonResponse($this->returnArray);
    }

    /**
     *
     * @param type $orderId
     * @return array
     */
    public function getCustomerPlanExpiryDate($customerPlan)
    {
        if (!empty($customerPlan)) {
            $customerPlanArray = unserialize($customerPlan);
            $duration = $customerPlanArray['duration'];
            $durationUnit = $customerPlanArray['duration_unit'];

            $shortDate = "+".$duration." ".$durationUnit;
            $increaseDate = strtotime($shortDate);
            $planExpiryDate = date("Y-m-d h:i:s", $increaseDate);
            return $planExpiryDate;
        }
    }
    
    /**
     *
     * @param type $customerId
     * @param type $groupId
     */
    public function assignCustomerGroup($customerId, $groupId)
    {
        if (!empty($customerId)) {
            $customer = $this->_customerRepository->getById($customerId);
            $customer->setGroupId($groupId);
            $this->_customerRepository->save($customer);
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
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

