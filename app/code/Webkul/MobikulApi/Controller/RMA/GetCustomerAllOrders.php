<?php
namespace Webkul\MobikulApi\Controller\RMA;

class GetCustomerAllOrders extends AbstractRma
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
            $orders=$this->getOrderCollection($this->customerId);
            $size=$orders->getSize();
            if($size > 0){
                $ordersdata=[];
                foreach($orders as $order){
                    $result=[
						"order_id"=>$order->getIncrementId(),
						"price"=>$order->getGrandTotal(),
						"date"=>$order->getCreatedAt()
                    ];
                    $ordersdata[]=$result;
                }
                $this->returnArray["data"]["orders"]=$ordersdata;
            }else{
                $this->returnArray["message"]="No order available for RMA request.";
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
    public function getOrder($orderId){
        return $this->orderRepository->get($orderId);
    }
    public function getOrdersOfCustomer($customerId)
    {
        $days = $this->getDefaultDays();
        $from = date('Y-m-d', strtotime("-".$days." days"));
        $allowedStatus = ['pending', 'processing', 'complete'];
        $orders = $this->_orderCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('customer_id', $customerId)
                        ->addFieldToFilter('status', ['in'=> $allowedStatus])
                        ->setOrder('created_at', 'desc');

        return $orders;
    }
    // public function getDefaultDays()
    // {
    //     $path = "rmasystem/parameter/days";
    //     $scope = ScopeInterface::SCOPE_STORE;
    //     $days = $this->scopeConfig->getValue($path, $scope);
    //     if ($days <= 0) {
    //         $days = 30;
    //     }

    //     return $days;
    // }
    // public function getAllowedStatus()
    // {
    //     $path = "rmasystem/parameter/allow_order";
    //     $scope = ScopeInterface::SCOPE_STORE;
    //     $days = (int) $this->scopeConfig->getValue($path, $scope);
    // }

    public function getOrderCollection($customerid)
    {
        
        //$this->_session->unsFilterData();
        //$this->_session->unsSortingSession();

        $allowedStatus =  $this->scopeConfig->getValue(
            'rmasystem/parameter/allow_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $allowedDays = $this->scopeConfig->getValue(
            'rmasystem/parameter/days',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        
            $joinTable = $this->detailsCollection->create()->getTable('sales_order');
            if ($allowedStatus == 'complete') {
                $collection = $this->_orderShipmentCollectionFactory->create();

                $collection->getSelect()->join(
                    $joinTable.' as so',
                    'main_table.order_id = so.entity_id',
                    ['grand_total', 'so.increment_id', 'so.created_at']
                )->where('main_table.customer_id ='.$customerid);

                $collection->addFilterToMap('created_at', 'so.created_at');
                $collection->addFilterToMap('customer_id', 'so.customer_id');
                $collection->addFilterToMap('increment_id', 'so.increment_id');
                $collection->addFieldToFilter('customer_id', $customerid);
                $collection->addFieldToFilter('so.status', 'complete');
            } else {
                $collection = $this->_orderCollectionFactory->create()
                    ->addFieldToFilter(
                        'customer_id',
                        $customerid
                    )
                    ->addFieldToFilter(
                        'status',
                        ['neq' => 'canceled']
                    )
                    ->addFieldToFilter(
                        'status',
                        ['neq' => 'closed']
                    );
            }
            if ($allowedDays != '') {
                $todaySecond = time();
                $allowedSeconds = $allowedDays * 86400;
                $pastSecondFromToday = $todaySecond - $allowedSeconds;
                $validFrom = date('Y-m-d H:i:s', $pastSecondFromToday);
                $collection->addFieldToFilter('created_at', ['gteq' => $validFrom]);
            }
            $collection->setOrder('entity_id', 'desc');
            $orderCollection = $collection;
        

        return $orderCollection;
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

