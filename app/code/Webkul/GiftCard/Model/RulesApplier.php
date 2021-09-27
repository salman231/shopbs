<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Model;

class RulesApplier extends \Magento\SalesRule\Model\RulesApplier
{

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\SalesRule\Model\Utility $utility
     * @param ChildrenValidationLocator $childrenValidationLocator
     */
    public function __construct(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $calculatorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Utility $utility,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\GiftCard\Model\ResourceModel\GiftDetail\CollectionFactory $giftData
    ) {
        $this->giftData = $giftData;
        $this->_customerSession = $customerSession->create();
        parent::__construct($calculatorFactory, $eventManager, $utility);
        $this->logger = $logger;
    }

    /**
     * Apply rules to current order item
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rules
     * @param bool $skipValidation
     * @param mixed $couponCode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function applyRules($item, $rules, $skipValidation, $couponCode)
    {
        $address = $item->getAddress();
        $appliedRuleIds = [];
        foreach ($rules as $rule) {
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }
            if (!$skipValidation && !$rule->getActions()->validate($item)) {
                $childItems = $item->getChildren();
                $isContinue = true;
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        if ($rule->getActions()->validate($childItem)) {
                            $isContinue = false;
                        }
                    }
                }
                if ($isContinue) {
                    continue;
                }
            }
            $this->validateData($skipValidation, $rule, $item);
            $this->applyRule($item, $rule, $address, $couponCode);
            $this->getRuleId($rule, $appliedRuleIds);
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        return $appliedRuleIds;
    }

    public function getRuleId($rule, $appliedRuleIds)
    {
        $customerLogin = $this->_customerSession->isLoggedIn();
        if ($customerLogin) {
            $customerEmail = $this->_customerSession->getCustomerData()->getEmail();
            $couponData =  $this->giftData->create()->addFieldToFilter('gift_code', $rule->getName());
            if ($couponData->getSize() >0) {
                foreach ($couponData as $coupons) {
                    if ($customerEmail == $coupons->getEmail()) {
                        $this->_customerSession->setReducedprice($rule->getDiscountAmount());
                        $this->_customerSession->setAmount($rule->getDiscountAmount());
                        $this->_customerSession->setCouponCode($rule->getName());
                        $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();
                    }
                }
            } else {
                $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();
            }
        }
        return $appliedRuleIds;
    }
}
