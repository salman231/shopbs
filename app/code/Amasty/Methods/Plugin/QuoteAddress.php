<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

namespace Amasty\Methods\Plugin;

class QuoteAddress extends \Amasty\Methods\Model\Manager
{
    protected $_scopeConfig;
    protected $_objectManager;
    protected $_structures = [];

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ){
        parent::__construct($request);
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_MembershipOrdersFactory = $MembershipOrdersFactory;
        $this->customerSession = $customerSession;
    }

    public function getMethodsStructure($websiteId)
    {
        if (!array_key_exists($websiteId, $this->_structures)){
            $this->_structures[$websiteId] = $this->_objectManager->create('\Amasty\Methods\Model\Structure\Shipping')
                ->load($websiteId);
        }
        return $this->_structures[$websiteId];
    }

    public function beforeRequestShippingRates(
        \Magento\Quote\Model\Quote\Address $address,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item = null
    ){
        $limitCarrier = $address->getLimitCarrier();

        $quote = $address->getQuote();

        $structure = $this->getMethodsStructure(
            $this->getWebsiteId($quote->getStore()->getWebsiteId())
        );

        if ($structure->getSize() > 0) {
            if (!is_array($limitCarrier)) {
                $limitCarrier = [$limitCarrier];
            }
            $carriers = $this->_scopeConfig->getValue(
                'carriers',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $quote->getStoreId()
            );
            
            

            $customerId = $this->customerSession->getCustomer()->getId();
            $data = array();
            if ($customerId) {
                $model = $this->_MembershipOrdersFactory->create()->getCollection();
                $model->addFieldToFilter('customer_id', $customerId);
                $model->addFieldToFilter('order_status', 'complete');
                $model->addFieldToFilter('plan_expiry_status', 0);
                $model->setOrder('membership_order_id', 'DESC');
                $data = $model->getData();
                
            }
            foreach ($carriers as $carrierCode => $carrierConfig) {
                $methodGroups = $structure->get($carrierCode);

                if ($methodGroups) {
                    $groupsIds = $methodGroups->getGroupIds();

                    if ($structure->validate(
                        $quote->getCustomerGroupId(),
                        $groupsIds)
                    ){
                        if(count($data) > 0 && $carrierCode == 'freeshipping'){
                            $limitCarrier[] = $carrierCode;
                        }
                        if(count($data) == 0 && $carrierCode != 'freeshipping'){
                            $limitCarrier[] = $carrierCode;    
                        }
                        
                    }
                    /*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $logger = $objectManager->get('\Psr\Log\LoggerInterface');
                    $logger->info(print_r("carrieretrttrt",true));
                    $logger->info(print_r($carrierCode,true));*/
                }
            }
            $address->setLimitCarrier($limitCarrier);
        }
    }
}