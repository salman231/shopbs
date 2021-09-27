<?php
namespace Webkul\MobikulApi\Controller\RewardPoints;
use Mirasvit\RewardsAdminUi\Model\System\Config\Source\Spend\Method;
use Mirasvit\Rewards\Model\Config\Source\Spending\ApplyTax;

class ApplyRewardPoints extends AbstractRewardPoints
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

            $purchase = $this->_rewardsPurchase->getPurchase($this->quoteId);

            if (empty($purchase->getQuote()) || !is_object($purchase->getQuote())) {
                $this->returnArray["inside_condition"]=true;
                /* @var $quote \Magento\Quote\Model\Quote */
                $quote = $this->quoteRepository->getActive($this->quoteId);
                //$quote = $this->cartFactory->create()->getQuoteById($this->quoteId);
                $purchase->setQuote($quote);
            }
            $pointAmount=$this->pointsAmount;
            //$this->request->setParams(['points_amount' => $pointsAmount]);
            
            $resultdata=$this->processApiRequest($purchase, $pointAmount, $this->quoteId);
            $this->returnArray["success"]=$resultdata["success"];
            $this->returnArray["message"]=$resultdata["message"];
            $this->emulate->stopEnvironmentEmulation($environment);
        } else {
            $this->returnArray["message"]="Invalid Customer.";
        }
        //$encodedData = $this->jsonHelper->jsonEncode($this->returnArray);
        return $this->getJsonResponse($this->returnArray);
    }
    public function processApiRequest($purchase, $pointAmount, $quoteid){
        if (!$purchase->getQuote()->getItemsCount()) {
            return [
                'success' => false,
                'message' => false,
            ];
        }

        $result = $this->process($purchase, $pointAmount, $quoteid);
        if (is_object($result['message'])) {
            $result['message'] = $result['message']->render();
        }

        return $result;
    }
    public function process($purchase, $pointAmount, $quoteid){
        $response     = [
            'success' => false,
            'message' => false,
        ];

        $pointsNumber = abs((int)$pointAmount);
        //$this->returnArray["amount"]=$pointsNumber;
        if ($this->removepoints == 1) {
            $pointsNumber = 0;
        }
        
        $oldPointsNumber = $purchase->getSpendPoints();
        //$oldPointsNumber = abs((int)$oldPointsNumber);
        // var_dump($oldPointsNumber);
        //$this->returnArray["old_amount"]=$oldPointsNumber;
        if ($pointsNumber <= 0 && $oldPointsNumber <= 0) {
            return $response;
        }

        try {
            $this->updatePurchase($purchase, $pointsNumber);

            //we should update purchase to show correct points number in cart and checkout rewards message
            $newPurchase = $this->_rewardsPurchase->getPurchase();
            if ($newPurchase) {
                $purchase = $newPurchase;
            }
            
            $response = $this->successResponse($purchase, $pointsNumber);
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = __('Cannot apply %1. %2 %3', $this->rewardsData->getPointsName(), $e->getMessage(), $e->getTraceAsString());

            //$this->context->getLogger()->error($e->getMessage());
        }
        $response['spend_points']          = $purchase->getSpendPoints();
        $response['spend_points_formated'] = $this->rewardsData->formatPoints($purchase->getSpendPoints());

        return $response;
    }
    public function updatePurchase($purchase, $pointsNumber) {
        if ($this->rewardsConfig->getAdvancedSpendingCalculationMethod() == Method::METHOD_ITEMS) {
            $purchase->getQuote()->setIncludeSurcharge(true);
            $purchase->setSpendPoints($pointsNumber)
                ->refreshPointsNumber(true)
                ->save();
            
            $purchase->getQuote()->setIncludeSurcharge(false);

            return;
        }

        $purchase
            ->setSaveItemIds(true)
            ->setSpendPoints($pointsNumber);

        if (!$pointsNumber) {
            $purchase->setBaseSpendAmount(0)
                ->setSpendAmount(0);
        }

        $purchase->save();

        $quote = $purchase->getQuote();
        if ($this->isApplyTaxAfterDiscount()) {
            $purchase->updatePoints(); // apply rewards discount
            $quote->setTotalsCollectedFlag(false); // recalculate tax with rewards discount
        }
        $quote->setTriggerRecollect(1);
        $quote->getBillingAddress();
        $quote->save();
        $this->quoteRepository->save($quote->collectTotals());
    }
    public function isApplyTaxAfterDiscount()
    {
        return $this->taxConfig->applyTaxAfterDiscount() &&
            $this->rewardsConfig->getGeneralApplyTaxAfterSpendingDiscount() == ApplyTax::APPLY_SPENDING_TAX_DEFAULT;
    }
    public function successResponse($purchase, $pointsNumber)
    {
        $response = [];
        if ($pointsNumber) {
            $response['success'] = true;
            $response['message'] = __(
                '%1 were applied.', $this->rewardsData->formatPoints($purchase->getSpendPoints())
            );

            // do not check max because max will be use instead of $pointsNumber
            if ($pointsNumber != $purchase->getSpendPoints() && $pointsNumber < $purchase->getSpendMinPoints()) {
                $response['success'] = false;
                $response['message'] = __(
                    'Minimum number is %1.', $this->rewardsData->formatPoints($purchase->getSpendMinPoints())
                );
            }
        } else {
            $response['success'] = true;
            $response['message'] = __('%1 were cancelled.', $this->rewardsData->getPointsName());
        }

        return $response;
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
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->pointsAmount = $this->wholeData["points_amount"] ?? 0;
            $this->removepoints = $this->wholeData["remove-points"] ?? 0;
            $this->storeId = $this->wholeData["storeId"] ?? 1;
            $this->forDashboard = $this->wholeData["forDashboard"] ?? 0;
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken);
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }
}

