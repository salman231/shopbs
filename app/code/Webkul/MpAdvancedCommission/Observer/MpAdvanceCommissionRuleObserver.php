<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul MpAdvancedCommission MpAdvanceCommissionRuleObserver Observer Model.
 */
class MpAdvanceCommissionRuleObserver implements ObserverInterface
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\MpAdvancedCommission\Helper\Data
     */
    protected $_commissionHelper;

    /**
     * @var \Webkul\MpAdvancedCommission\Model\CommissionRulesFactory
     */
    protected $_commissionRules;

     /**
      * @param \Magento\Framework\Event\Manager $eventManager
      * @param \Magento\Framework\ObjectManagerInterface $objectManager
      * @param \Magento\Customer\Model\Session $customerSession
      * @param \Webkul\MpAdvancedCommission\Helper\Data $commissionHelper
      * @param \Webkul\MpAdvancedCommission\Model\CommissionRulesFactory $commissionRules
      */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAdvancedCommission\Helper\Data $commissionHelper,
        \Webkul\MpAdvancedCommission\Model\CommissionRulesFactory $commissionRules,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_commissionHelper=$commissionHelper;
        $this->_commissionRules=$commissionRules;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Observer to calculate the admin commission based on commission rules
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $orderInstance Order */
        $order = $observer->getOrder();

        $helper = $this->_commissionHelper;

        $sellerData = $helper->getSellerData($order);
        $commission = [];
        if (!$helper->getUseCommissionRule()) {
            $this->_customerSession->setData('advancecommissionrule', $commission);
            return false;
        }
        $isPriceRoundoff = $this->scopeConfig->getValue(
            'mpadvancedcommission/options/commission_calculation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        foreach ($sellerData as $sellerId => $row) {
            foreach ($row['details'] as $item) {
                $commissionRuleCollection = $this->_commissionRules->create()->getCollection();
                if ($isPriceRoundoff) {
                    $commissionRuleCollection = $commissionRuleCollection
                    ->addFieldToFilter(
                        "price_from",
                        ["lteq" => round($item['price'])]
                    )
                    ->addFieldToFilter(
                        "price_to",
                        ["gteq" => round($item['price'])]
                    );
                } else {
                    $commissionRuleCollection = $commissionRuleCollection
                    ->addFieldToFilter(
                        "price_from",
                        ["lteq" => $item['price']]
                    )
                    ->addFieldToFilter(
                        "price_to",
                        ["gteq" => $item['price']]
                    );
                }
                
                if (empty($commissionRuleCollection->getSize())) {
                    if ($isPriceRoundoff) {
                        $commissionRuleCollection = $commissionRuleCollection
                        ->addFieldToFilter(
                            "price_from",
                            ["lteq" => round($row['total'])]
                        )
                        ->addFieldToFilter(
                            "price_to",
                            ["gteq" => round($row['total'])]
                        );
                    } else {
                        $commissionRuleCollection = $commissionRuleCollection
                        ->addFieldToFilter(
                            "price_from",
                            ["lteq" => $row['total']]
                        )
                        ->addFieldToFilter(
                            "price_to",
                            ["gteq" => $row['total']]
                        );
                    }
                }
                foreach ($commissionRuleCollection as $commissionRule) {
                    if ($commissionRule->getCommissionType() != "percent") {
                        if ($item['price'] > 0) {
                            $commission[$item['item_id']] = [
                                "amount" => $commissionRule->getAmount() > $row['total'] ?
                                 $row['total'] : $commissionRule->getAmount(),
                                "type" => $commissionRule->getCommissionType()
                            ];
                        } else {
                            $commission[$item['item_id']] = [
                                "amount" => 0,
                                "type" => $commissionRule->getCommissionType()
                            ];
                        }
                            
                    } else {
                        $commission[$item['item_id']] = [
                            "amount" => $commissionRule->getAmount(),
                            "type" => $commissionRule->getCommissionType()
                        ];
                    }
                    break;
                }
            }
        }
        $this->_customerSession->setData('advancecommissionrule', $commission);
    }
}
