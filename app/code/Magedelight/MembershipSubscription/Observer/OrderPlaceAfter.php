<?php
/**
* Magedelight
* Copyright (C) 2017 Magedelight <info@magedelight.com>
*
* @category Magedelight
* @package Magedelight_MembershipSubscription
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\MembershipSubscription\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class OrderPlaceAfter implements ObserverInterface
{
    /**
     * Http Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     *
     * @var session
     */
    protected $session;
    
    /**
     * Membership factory
     *
     * @var \Magedelight\MembershipSubscription\Model\MembershipProductsFactory
     */
    protected $_MembershipProductsFactory;

    /**
     *
     * @var MembershipOrdersFactory
     */
    protected $_MembershipOrdersFactory;
    /**
     * @var Cart
     */
    protected $_cart;

    /**
     *
     * @var OrderInterface
     */
    protected $_order;
    
    /**
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $session,
        \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory,
        \Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        array $data = []
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->_MembershipProductsFactory = $MembershipProductsFactory;
        $this->_MembershipOrdersFactory = $MembershipOrdersFactory;
        $this->_cart = $cart;
        $this->_order = $order;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->groupRepository = $groupRepository;
    }
    
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderids = $observer->getEvent()->getOrderIds();

        foreach ($orderids as $orderid) {
            $order = $this->_order->load($orderid);
            $currentOrder = $order->getData();
            // echo "<pre>";
            // print_r($currentOrder);
            // exit();
            
            $itemCollection = $order->getItemsCollection();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            // echo "<pre>";
            /*$itemCollection1 = $order->getItemsCollection()->getData();
                        print_r($itemCollection1);
                        exit();*/
            foreach ($itemCollection as $item) {
                
                if (isset($currentOrder['status']) && (!empty($item)) && ($item->getProductType() == "Membership")) {
                    $options = $item->getProductOptions();
                    $productId = $item->getProductId();
                    
                    $orderStatus = $order->getStatus();
                    


                       
                    
                    if ($productId) 
                    {
                        $membershipProduct = $this->getMembershipProduct($productId);
                        
                        // $product_options = json_decode($options,TRUE);
                        $product_options = $options;
                        
                        $customer_plan = $product_options['info_buyRequest']['duration_option'];

                         /*print_r($options);
                        exit();*/
                        
                        $payment = $order->getPayment();

                        $customerPlanArray = unserialize($customer_plan);
                        $duration = $customerPlanArray['duration'];
                        $durationUnit = $customerPlanArray['duration_unit'];

                        $shortDate = "+".$duration." ".$durationUnit;
                        $increaseDate = strtotime($shortDate);
                        $planExpiryDate = date("Y-m-d h:i:s", $increaseDate);

                        
                        $data = ['membership_product_id'=>$membershipProduct['membership_product_id'],
                                'product_id'=>$productId,
                                'order_id'=>$item->getOrderId(),
                                'order_status'=>$currentOrder['status'],
                                'customer_id'=>$currentOrder['customer_id'],
                                'customer_email'=>$currentOrder['customer_email'],
                                'customer_plan'=>$customer_plan,
                                'price'=>$item->getPrice(),
                                // 'price'=>$order->getGrandTotal(),
                                'related_customer_group_id'=>$membershipProduct['related_customer_group_id'],
                                'plan_expiry_date'=>$planExpiryDate];

                        if($duration > 1 && $payment->getMethod() == 'facpayment'){
                            
                            $tableName = $resource->getTableName('recurring_order');
                            // print_r(unserialize($customer_plan));
                            $planperiod = unserialize($customer_plan);
                            $today = strtotime("now");
                            
                             $start_week = date("d",$today);
                            $recurringdata = array();
                            for ($i = 1; $i < $planperiod['duration']; $i++) 
                            {
                                $order_id = $item->getOrderId();
                                $customer_id = $currentOrder['customer_id'];
                                $increment_id = $currentOrder['increment_id'];
                                $customer_email = $currentOrder['customer_email'];
                                $date = date("Y-m-d", strtotime( date( 'Y-m-'.$start_week )." +$i months"));
                                $price = $item->getPrice();
                                // $recurringdata []=  $months;
                                $sql = "INSERT INTO " . $tableName . "(order_id,increment_id,customer_id,customer_email,next_date,price) VALUES (".$order_id.",'".$increment_id."',".$customer_id.",'".$customer_email."','".$date."',".$price.")";
                                // echo $sql."<br>";
                                $connection->query($sql);
                            }
                        }
                        
                        if (count($data)>0) {
                            $model = $this->_MembershipOrdersFactory->create();
                            $model->addData($data);
                            $model->save();

                            $customer_id = $this->customerSession->getCustomer()->getId();
                            $customer = $this->customerRepository->getById($customer_id);
                            $group_id = $membershipProduct['customer_group_id'];
                            $customer->setGroupId($group_id);
                            $this->customerRepository->save($customer);

                        }
                    }
                }
            }
        }
    }
    
    /**
     *
     * @param type $productId
     * @return type
     */
    public function getMembershipProduct($productId)
    {
        $model = $this->_MembershipProductsFactory->create();
        $model->load($productId, 'product_id');
        return $model->getData();
    }
}
