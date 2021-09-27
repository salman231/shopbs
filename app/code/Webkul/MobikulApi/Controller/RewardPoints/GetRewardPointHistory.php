<?php
namespace Webkul\MobikulApi\Controller\RewardPoints;

class GetRewardPointHistory extends AbstractRewardPoints
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
            $transactions = $this->getTransactions($this->customerId);
            //var_dump($transactions);
            //exit();
            //$size = count($transactions);
            $transactionData=[];
            foreach($transactions as $transaction){
                $result=[
                    "id" => $transaction->getId(),
                    "amount" => $transaction->getAmount(),
                    "amount_used" => $transaction->getAmountUsed(),
                    "comment" => $transaction->getComment(),
                    "code" => $transaction->getCode(),
                    "is_expired" => $transaction->getIsExpired(),
                    "is_expiration_email_sent" => $transaction->getIsExpirationEmailSent(),
                    "expires_at" => $transaction->getExpiresAt(),
                    "created_at" => $transaction->getCreatedAt(),
                    "activated_at" => $transaction->getActivatedAt(),
                    "is_activated" => $transaction->getIsActivated()
                ];
                $transactionData[]=$result;
            }
            $this->returnArray["data"]["transactions"]=$transactionData;
            //$this->returnArray["transactions"]=$transactions;
            $this->emulate->stopEnvironmentEmulation($environment);
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    public function getTransactions($customerId, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null){
        if ($searchCriteria) {
            $customerSearchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $customerId)->create();
            $groups = $searchCriteria->getFilterGroups();
            foreach ($customerSearchCriteria->getFilterGroups() as $filterGroup) {
                $groups[] = $filterGroup;
            }
            $searchCriteria->setFilterGroups($groups);
        } else {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $customerId)->create();
        }

        return $this->transactionRepository->getList($searchCriteria)->getItems();
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

