<?php
namespace Magedelight\MembershipSubscription\Model\Customer;
use Magedelight\MembershipSubscription\Api\Customer\GetCustomerSubscribedMembershipInterface;
class GetCustomerSubscribedMembership implements GetCustomerSubscribedMembershipInterface
{
    /**
    * Membership factory
    *
    * @var \Magedelight\MembershipSubscription\Model\MembershipProductsFactory
    */
   protected $_MembershipProductsFactory;
   
   /**
    * Membership factory
    *
    * @var MembershipOrdersFactory
    */
   protected $_MembershipOrdersFactory;
   
   
   /**
    *
    * @var ProductFactory
    */
   protected $_productloader;
   
   /**
    *
    * @var PriceCurrencyInterface
    */
   protected $_priceCurrency;

   /**
    * Customer group
    *
    * @var \Magento\Customer\Model\ResourceModel\Group\Collection
    */
   protected $_customerGroup;
   
   /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
   protected $scopeConfig;
   protected $_customerRepositoryInterface;
   
   /**
    *
    * @param \Magento\Catalog\Block\Product\Context $context
    * @param \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory
    * @param \Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory
    * @param \Magento\Catalog\Model\ProductFactory $_productloader
    * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param array $data
    */
   public function __construct(
       \Magento\Catalog\Block\Product\Context $context,
       \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory,
       \Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory,
       \Magento\Catalog\Model\ProductFactory $_productloader,
       \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
       \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
       \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
   ) {
   
       $this->_MembershipProductsFactory = $MembershipProductsFactory;
       $this->_MembershipOrdersFactory = $MembershipOrdersFactory;
       $this->_productloader = $_productloader;
       $this->_priceCurrency = $priceCurrency;
       $this->_customerGroup = $customerGroup;
       $this->_customerRepositoryInterface = $customerRepositoryInterface;
       $this->scopeConfig = $context->getScopeConfig();
   }

    /**
  	*Get all subscribed membership of customer by customer Id.
  	*
  	* @param int $cid
  	* @return string[]
  	*/
  	public function getcustomersubscribedMembership($cid){

        if ($cid) {
            $result=array();
			$result['response_code']=array(
				"response_code"=> true
			);
            $model = $this->_MembershipOrdersFactory->create()->getCollection();
            $model->addFieldToFilter('customer_id', $cid);
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
                $result['data']=array();
                $result['data']['membershipinfo']=array(
                    "Package Name" => $this->getCustomerGroup($cid),
                    "Expiry Date" => $expiryDate,
                    "Package Description" => $product->getDescription() 
                );
                
                $paymentHistory = $this->getPaymentHistory($cid);
                $currencySymbol = $this->getCurrentCurrencySymbol();
                if (count($paymentHistory)>0) {
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
                        $result['data']['planinfo']=array(
                            "Plan Name" => $history['product_name'],
                            "Order Date" => $date,
                            "Duration" => $duration,
                            "Price" => $currencySymbol,
                            "Order Status" => $history['order_status'],
                            "Plan Status" => $planStatus
                        );
                    }
                }
                else{
                    $result['data']=array(
                        "message" => "No Plan History Found"
                    );
                }

            } else {
                $result['data']=array(
                    "message" => "Currently you are not a member of any membership."
                );
            }
        } else {
            $result['data']=array(
                "message" => "Invalid Customer."
            );
        }
        return $result;

    }
     /**
     * @param int $customerid
     * @return string
     */
    public function getCustomerGroup($customerid)
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

    /**
     *
     * @param type $customerGroupId
     * @return boolean
     */
    public function getProduct($customerGroupId)
    {
        if (isset($customerGroupId)) {   
            $model = $this->_MembershipProductsFactory->create();
            $model->load($customerGroupId, 'customer_group_id');
            $productId = $model->getProductId();
            
            return $this->_productloader->create()->load($productId);
        }
        return false;
    }
    
    /**
     *
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        return $this->_priceCurrency->getCurrency()->getCurrencySymbol();
    }
    
    /**
     * @param int $customerid
     * @return array
     */
    public function getPaymentHistory($customerid)
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
     
    /**
     *
     * @return HTTP
     */
    public function getHref()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $url = trim($this->scopeConfig->getValue('membership/membership_settings/identifier', $storeScope));
        return $this->getUrl($url);
    }
    
    /**
     *
     * @param type $productId
     * @return type
     */
    public function getDiscountableProductsUrl($productId)
    {
        
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        return $baseUrl."md_membership/view/membership/id/".$productId;
    }

    /**
     *
     * @return type
     */
    public function getBeforeDay()
    {
        return $duration = (int)$this->scopeConfig->getValue('membership/general/mail_before_days', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}