<?php
namespace Webkul\MobikulApi\Controller\RewardPoints;

class GetCustomerRewardPoints extends AbstractRewardPoints
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

            $collection = $this->transactionCollectionFactory->create();
            $collection->addCustomerFilter($this->customerId);
            $collection->addActivatedFilter();
            $collection->getSelect()->columns(new \Zend_Db_Expr("SUM(amount) as balance"));
    
            $balance= (int)$collection->getFirstItem()->getData('balance');
            $this->returnArray["balance"]=$balance;
           
            $this->emulate->stopEnvironmentEmulation($environment);
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
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

