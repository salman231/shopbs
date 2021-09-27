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

namespace Magedelight\MembershipSubscription\Plugin\Model\Checkout\Cart;

use Magento\Framework\Exception\StateException;

class ManageCart
{
     /**
      * @var \Magento\Quote\Model\Quote
      */
    protected $quote;
    
    /**
     * Membership factory
     *
     * @var \Magedelight\MembershipSubscription\Model\MembershipProductsFactory
     */
    protected $_MembershipProductsFactory;

    /**
     *
     * @var ManagerInterface
     */
    protected $_messageManager;
    
    /**
     * Http Request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory,
        \Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->request = $request;
        $this->_messageManager = $messageManager;
        $this->quote = $checkoutSession->getQuote();
        $this->_MembershipOrders = $MembershipOrdersFactory;
        $this->_MembershipProductsFactory = $MembershipProductsFactory;
        $this->_customerSession = $customerSession;
    }

    /**
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param int $data
     * @return type
     */
    public function beforeupdateItems(\Magento\Checkout\Model\Cart $subject, $data)
    {
        $durationOption = $this->request->getParam('duration_option');
        
        if (!isset($durationOption)) {
            foreach ($data as $key => $value) {
                if ($key) {
                    $quote = $subject->getQuote();
                    $item = $quote->getItemById($key);
                    $productType = $item->getProductType();

                    if ($productType == "Membership") {
                        $data[$key]['qty'] = 1;
                        if ($item->getQty() > 0) {
                            $this->_messageManager->addNoticeMessage('Membership Plan is not allowed to purchase with other products. Kindly do seprate transaction.');
                        }
                    }
                }
            }
        }
       
        return [$data];
    }
    
    
    /**
     * beforeAddProduct
     *
     * @param      $subject
     * @param      $productInfo
     * @param null $requestInfo
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        $customerGroup = $this->_customerSession->getCustomer()->getGroupId();
      /*  if($customerGroup == 4){
            // return $this->_messageManager->addError(__('Seller cannot allow to add membership plan.'));
            throw new StateException(__('Seller cannot allow to add membership plan.'));
    
        }*/
        $customerId =  $this->_customerSession->getCustomer()->getId();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $sellerModels = $objectManager->get ( 'Webkul\Marketplace\Model\Seller' );
        $sellerdata = $sellerModels->load ($customerId, 'customer_id' );
        $model = $this->_MembershipOrders->create()->getCollection();
            $model->addFieldToFilter('customer_id', $customerId);
            $model->addFieldToFilter('order_status', 'complete');
            $model->addFieldToFilter('plan_expiry_status', 0);
            $model->setOrder('membership_order_id', 'DESC');

            if ($productInfo->getTypeId() == "Membership" &&count($model) > 0){
                throw new StateException(__('You have already purchased membership plan.'));
            }

            if(count($sellerdata->getData()) > 1 && $sellerdata->getStatus() == 1 && $productInfo->getTypeId() == "Membership" ){
                // return $this->_messageManager->addError(__('Seller cannot allow to add membership plan.'));
                // throw new StateException(__('Seller cannot allow to add membership plan.'));
                throw new StateException(__('No Prime Membership for Suppliers. They will need to open a customers account to buy a Prime Subscription. '));
            }
        $protype = array('giftcard', 'Membership','downloadable','virtual');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseurl = $storeManager->getStore()->getBaseUrl();
        foreach ($this->quote->getAllVisibleItems() as $item) {

            if($item->getProductType() == "Membership") {
            // if(in_array($item->getProductType(), $protype)){
                $message = __('Subscription Products must be bought separately as a single item in the shopping cart. Please add any extra items to your wish list to buy in a separate transaction.');
                throw new StateException($message);
                /*throw new \Magento\Framework\Exception\LocalizedException(
                	__('You cannot add more than one product for membership plan to cart.')
            	);*/
            }                        
        }
        if ($productInfo->getTypeId() == "Membership") {
        // if(in_array($productInfo->getTypeId(), $protype)){
            $allItems =  $this->quote->getAllVisibleItems();
            
            $deleteItem = [];
            foreach ($allItems as $item) {
                $membershipProducts = $this->getMembershipProducts();
                $productId = $item->getProductId();

                if (in_array($productId, $membershipProducts)) {
                    $deleteItem[] = $item->getItemId();
                }
                if($item->getProductType() !== "Membership") {
                // if(!in_array($item->getProductType(), $protype)) {
                    //$this->_messageManager->addError(__('You cannot add membership plan with another product.'));
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Subscription products must be bought separately as a single item in the shopping cart, please complete this transaction and buy your subscription in a separate transaction.')
                	);
                }
            }

            if (count($deleteItem) > 0) {
                //$this->_messageManager->addError(__('You cannot add more than one membership plan to cart.'));
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You cannot add more than one membership plan to cart.')
                );
            }
        }

	   return [$productInfo, $requestInfo];
    }
    
    
    /**
     * get membership product
     * @return Array
     */
    public function getMembershipProducts()
    {
        $model = $this->_MembershipProductsFactory->create()->getCollection();
        $modelData = $model->getData();
        foreach ($modelData as $key => $value) {
            $membershipProducts[] = $value['product_id'];
        }
        
        return $membershipProducts;
    }
    
    /**
     * create item model
     */
    public function getItemModel()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $itemModel = $objectManager->create('Magento\Quote\Model\Quote\Item');
        return $itemModel;
    }
}
