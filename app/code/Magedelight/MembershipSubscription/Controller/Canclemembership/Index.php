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

namespace Magedelight\MembershipSubscription\Controller\Canclemembership;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $scopeConfig;
    
    const MD_LAYER = 'mdlayer';

    /**
     *
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\DataObject $requestObject
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryFactory $category
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\DataObject $requestObject,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $category,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Webkul\Marketplace\Model\Seller $seller,
        \Magento\Checkout\Model\Cart $modelCart,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_requestObject = $requestObject;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $registry;
        $this->storeManager = $storeManager;
        $this->category = $category;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->catalogSession = $catalogSession;
        $this->coreRegistry = $coreRegistry;
        $this->order = $order;
        $this->invoice = $invoice;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->_MembershipOrdersFactory = $MembershipOrdersFactory;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->_seller = $seller;
        $this->_modelCart = $modelCart;
        $this->redirect = $context->getRedirect();
        $this->checkoutSession = $checkoutSession;

    }
    
   
    /**
     *
     * @return result page
     */
    public function execute()
    {
        
        
        $id = $this->getRequest()->getParams();
        $order = $this->order->load($id);
        $invoice = $this->invoice;
        $creditMemoFacory = $this->creditmemoFactory;
        $creditmemoService = $this->creditmemoService;

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $incrementId = $order->getIncrementId();

        $customerId = $this->customerSession->getCustomer()->getId();
        if ($customerId) {

            $model = $this->_MembershipOrdersFactory->create()->getCollection();
            $model->addFieldToFilter('customer_id', $customerId);
            $model->addFieldToFilter('order_status', 'complete');
            $model->addFieldToFilter('plan_expiry_status', 0);
            $model->addFieldToFilter('order_id', $id);
            
            $data = $model->getData();
           
            if(count($data) == 0){
                $this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'));
                $resultRedirect->setPath('md_membership/membership/plan/');
                return $resultRedirect;
            }

           /* echo "<pre>";
            print_r($data);
            exit();
            */
            try {
                /*if ($history['order_status']!="pending") {
                    if ($history['plan_expiry_status']==0) {
                        $planStatus = "Active Plan";
                    } else {
                        $planStatus = "Expired Plan";
                    }
                }*/

                foreach ($data as $key => $planvalue) {
                    // $planstatus = $planvalue['order_status'];
                    if ($planvalue['order_status']!="pending") 
                    {
                        if ($planvalue['plan_expiry_status']==0) 
                        {
                            $planStatus = $planvalue['order_status'];
                        
                        } else {
                            $planStatus = $planvalue['order_status'];
                        }
                        // echo $planStatus."<br>";
                    }
                }
                if($planStatus == 'complete'){
                    $invoices = $order->getInvoiceCollection();
                    foreach ($invoices as $invoice) {
                        $invoiceincrementid = $invoice->getIncrementId();
                    }

                    $invoiceobj = $invoice->loadByIncrementId($invoiceincrementid);
                    $creditmemo = $creditMemoFacory->createByOrder($order);

                    // Don't set invoice if you want to do offline refund
                    // $creditmemo->setInvoice($invoiceobj);

                    $creditmemoService->refund($creditmemo); 

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    $tableName = $resource->getTableName('recurring_order');

                    $update = "Update " . $tableName . " Set `execute` = 1 WHERE order_id = ".$order->getId()." OR customer_id =".$order->getCustomerId();
                    $connection->query($update);

                    // echo "CreditMemo Succesfully Created For Order: ".$incrementId;
                     

                }
                if ($order instanceof \Magento\Framework\Model\AbstractModel) {
                    if ($order->getState() == 'closed') {
                        foreach ($order->getAllItems() as $item) {
                            if ((count($item)>0) && ($item->getProductType() == "Membership") && ($item->getQtyRefunded()>0)) {
                                $orderId = $item->getOrderId();
                                $Ordersmodel = $this->_MembershipOrdersFactory->create();
                                $Ordersmodel->load($orderId, 'order_id');
                                $membershipOrder = $Ordersmodel->getData();
                                $data = [
                                    'order_status'=>$order->getState(),                            
                                    'plan_expiry_status'=>1,
                                    'plan_expiry_date'=>date("Y-m-d h:i:s")
                                ];
                                $Ordersmodel->addData($data);
                                $Ordersmodel->save();
                                $this->assignCustomerGroup($order->getCustomerId(), $membershipOrder['customer_past_group_id']);
                                $order->setCustomerGroupId($membershipOrder['customer_past_group_id']);                        
                            }
                        }
                    }
                }
                if ($order instanceof \Magento\Framework\Model\AbstractModel) {
                    if ($order->getState() == 'complete') {
                        foreach ($order->getAllItems() as $item) {
                            if ((count($item)>0) && ($item->getProductType() == "Membership")) {
                                $orderId = $item->getOrderId();
                                $Ordersmodel = $this->_MembershipOrdersFactory->create();
                                $Ordersmodel->load($orderId, 'order_id');
                                $membershipOrder = $Ordersmodel->getData();
                                
                                if (count($membershipOrder)>0) {
                                    $customerId = $order->getCustomerId();
                                    $orderStatus = $order->getState();
                                    $customerPastGroup = $order->getCustomerGroupId();
                                    $planExpiryDate = $this->getCustomerPlanExpiryDate($membershipOrder['customer_plan']);
                                    
                                    $groupId = $membershipOrder['related_customer_group_id'];
                                    
                                    if ($planExpiryDate) {
                                        $data = ['order_status'=>$orderStatus,'customer_past_group_id'=>$customerPastGroup,'current_customer_group_id'=>$groupId,'plan_expiry_date'=>$planExpiryDate];
                                        $Ordersmodel->addData($data);
                                        $Ordersmodel->save();
                                    }
                                    $customer = $this->customerRepository->getById($customerId);
                                    $sellercoll = $this->_seller->getCollection();  
                                    $sellercoll->addFieldToFilter('customer_id',$customerId)
                                    ->addFieldToFilter('status',1);
                                    if(count($sellercoll) > 0){
                                        $groupId = 4;
                                    }else{
                                        $groupId = 1;
                                    }
                                    
                                    $this->assignCustomerGroup($customerId, $groupId);
                                    $order->setCustomerGroupId($groupId);
                                }
                            }
                        }
                    }
                }
                $this->messageManager->addSuccess(__("Membership Plan has been canceled: ".$incrementId));

                $cart = $this->_modelCart;
                $quoteItems = $this->checkoutSession->getQuote()->getItemsCollection();
                $quote = $this->checkoutSession->getQuote();
                $quote->setIsActive(0);
                $quote->save();
                foreach($quoteItems as $item)
                {
                    $cart->removeItem($item->getId())->save(); 
                }
                $this->customerSession->logout()
                     ->setBeforeAuthUrl($this->redirect->getRefererUrl())
                     ->setLastCustomerId($customerId);
                return $resultRedirect->setPath('customer/account/logoutSuccess/');

            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));   

                // $this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'));   
            }

        } 
        $resultRedirect->setPath('md_membership/membership/plan/');
        return $resultRedirect;
    }

    /**
     *
     * @param type $orderId
     * @return array
     */
    public function getCustomerPlanExpiryDate($customerPlan)
    {
        if (!empty($customerPlan)) {
            $customerPlanArray = unserialize($customerPlan);
            $duration = $customerPlanArray['duration'];
            $durationUnit = $customerPlanArray['duration_unit'];

            $shortDate = "+".$duration." ".$durationUnit;
            $increaseDate = strtotime($shortDate);
            $planExpiryDate = date("Y-m-d h:i:s", $increaseDate);
            return $planExpiryDate;
        }
    }
    
    /**
     *
     * @param type $customerId
     * @param type $groupId
     */
    public function assignCustomerGroup($customerId, $groupId)
    {
        if (!empty($customerId)) {
            $customer = $this->customerRepository->getById($customerId);
            $customer->setGroupId($groupId);
            $this->customerRepository->save($customer);
        }
    }
}
