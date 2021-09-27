<?php
namespace Webkul\MobikulApi\Controller\RewardPoints;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;
use Mirasvit\Rewards\Model\Config\Source\Spending\ApplyTax;

class GetMaxRewardPointsToSpend extends AbstractRewardPoints
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
            if($this->quoteId == 0){
                $quote = $this->helper->getCustomerQuote($this->customerId);
                $this->quoteId = $quote->getId();
            }

            $purchase = $this->_rewardsPurchase->getByQuote($this->quoteId);

            if (empty($purchase->getQuote()) || !is_object($purchase->getQuote())) {
                //$this->returnArray["inside_condition"]=true;
                /* @var $quote \Magento\Quote\Model\Quote */
                $quote = $this->quoteRepository->getActive($this->quoteId);
                //$quote = $this->cartFactory->create()->getQuoteById($this->quoteId);
                $purchase->setQuote($quote);
            }
            $this->returnArray["maxnoofpointstospend"]=$purchase->getSpendMaxPoints() > 0 ? $purchase->getSpendMaxPoints() : 0;
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
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

