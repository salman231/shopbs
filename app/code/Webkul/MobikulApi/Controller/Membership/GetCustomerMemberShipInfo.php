<?php
namespace Webkul\MobikulApi\Controller\Membership;

class GetCustomerMemberShipInfo extends AbstractMembership
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
            $model = $this->_MembershipOrdersFactory->create()->getCollection();
            $model->addFieldToFilter('customer_id', $this->customerId);
            $model->addFieldToFilter('order_status', 'complete');
            $model->addFieldToFilter('plan_expiry_status', 0);
            $model->setOrder('membership_order_id', 'DESC');
            $data = $model->getData();
            if (count($data)>0) {
                $membershipData=$data[0];

                if ($membershipData['plan_expiry_date']=="" || $membershipData['plan_expiry_date']=="0000-00-00 00:00:00") {
                    $expiryDate = "Not Available";
                } else {
                    $expiryDate = date('Y-m-d', strtotime($membershipData['plan_expiry_date']));
                }
                
                $beforeDay = $this->getBeforeDay();
                
                $shortDate = "+".$beforeDay." Day";
                $newDate = strtotime($shortDate);
                $increaseDate = date("Y-m-d", $newDate);
                
                
                $customerGroupId = $membershipData['current_customer_group_id'];
                $product = $this->getProduct($customerGroupId);
                //$result['data']=array();
                $membershipinfo=[
                    "Package Name" => $this->getCustomerGroup($this->customerId),
                    "Expiry Date" => $expiryDate,
                    "Package Description" => $product->getDescription()
                ];
                $this->returnArray["data"]["membershipinfo"]=$membershipinfo;
                //$result['data']['membershipinfo']=
                
                $paymentHistory = $this->getPaymentHistory($this->customerId);
                $currencySymbol = $this->getCurrentCurrencySymbol();
                if (count($paymentHistory)>0) {
                    $paymentHistoryData=[];
                    foreach ($paymentHistory as $history) {
                        $customerPlan = unserialize($history['customer_plan']);
                        $duration = $customerPlan['duration']." ".$customerPlan['duration_unit'];
                
                        if ($history['order_status']!="pending") {
                            if ($history['plan_expiry_status']==0) {
                                $planStatus = "Active Plan";
                            } else {
                            $planStatus = "Expired Plan";
                            }
                        } else {
                            $planStatus = "Pending";
                        }
                        $date=date('Y-m-d', strtotime($history['created_at']));
                        $currencySymbol=$currencySymbol.$customerPlan['price'];
                        $planinfo=[
                            "Plan Name" => $history['product_name'],
                            "Order Date" => $date,
                            "Duration" => $duration,
                            "Price" => $currencySymbol,
                            "Order Status" => $history['order_status'],
                            "Plan Status" => $planStatus
                        ];
                        
                        $paymentHistoryData[]=$planinfo;
                        
                    }
                    $this->returnArray["data"]["planinfo"]=$paymentHistoryData;
                }
                else{
                    $this->returnArray["message"]="No Plan History Found";
                    
                }

            } else {
                $reqpath = $this->__scopeConfig->getValue(
                    "membership/membership_settings/identifier",
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                $UrlRewriteCollection=$this->_urlRewrite->getCollection()->addFieldToFilter('request_path', $reqpath);
    	        $Item = $UrlRewriteCollection->getFirstItem(); 
                if ($UrlRewriteCollection->getFirstItem()->getId()) {
                    // target path does exist
                    $path=$Item->getTargetPath();
                    $array=explode('/',$path);
                    $id=(int) end($array);
                    $this->returnArray["membership_product_id"]=$id;
                }
                $this->returnArray["message"]="Currently you are not a member of any membership.";
                 
            }
            $this->emulate->stopEnvironmentEmulation($environment);
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    
    protected function getCustomerGroup($customerid)
    {
        $customer = $this->_customerRepositoryInterface->getById($customerid);
        $currentGroup = $customer->getGroupId();
        $customerGroups = $this->_customerGroup->toOptionArray();
        foreach ($customerGroups as $group) {
            if ($group['value']==$currentGroup) {
                return $group['label'];
            }
        }
    }

    
    protected function getProduct($customerGroupId)
    {
        if (isset($customerGroupId)) {   
            $model = $this->_MembershipProductsFactory->create();
            $model->load($customerGroupId, 'customer_group_id');
            $productId = $model->getProductId();
            
            return $this->_productloader->create()->load($productId);
        }
        return false;
    }
    
    
    protected function getCurrentCurrencySymbol()
    {
        return $this->_priceCurrency->getCurrency()->getCurrencySymbol();
    }
    
    
    protected function getPaymentHistory($customerid)
    {
        //$page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 10;
        //$pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 10;
        //$customerId = $this->getCurrentCustomer()->getId();
        if ($customerid) {
            $collection = $this->_MembershipOrdersFactory->create()->getCollection();
            $collection->addFieldToFilter('customer_id', $customerid);
            $collection->setOrder('membership_order_id', 'DESC');
            $collection->getSelect()->join('magedelight_membership_products as products', 'main_table.membership_product_id = products.membership_product_id', 'product_name');
            //$collection->setPageSize($pageSize);
            //return $collection->setCurPage($page);
            
            $data = $collection->getData();
            return $data;
        }
    }

    protected function getBeforeDay()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = (int)$objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('membership/general/mail_before_days', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $config;
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

