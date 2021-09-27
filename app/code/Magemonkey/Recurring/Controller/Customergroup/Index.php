<?php

namespace Magemonkey\Recurring\Controller\Customergroup;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	/**
	* @var \Magento\Framework\App\Config\ScopeConfigInterface
	*/
	protected $scopeConfig;

	/**
	* @var \Magento\Store\Model\StoreManagerInterface
	*/
	protected $storeManager;
	/**
	* @var \Magento\Framework\Escaper
	*/
	protected $_escaper;
	/**
	* @param \Magento\Framework\App\Action\Context $context
	* @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
	* @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
	* @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	* @param \Magento\Store\Model\StoreManagerInterface $storeManager
	*/
	public function __construct(
	\Magento\Framework\App\Action\Context $context,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory,
	\Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory,
	\Magento\Customer\Model\Session $customerSession,
    \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
    \Webkul\Marketplace\Model\Seller $seller,
	\Magento\Framework\Escaper $escaper
	) {
	parent::__construct($context);
	$this->scopeConfig = $scopeConfig;
	$this->storeManager = $storeManager;
	$this->_MembershipOrders = $MembershipOrdersFactory;
	$this->_MembershipProducts = $MembershipProductsFactory;
	$this->_seller = $seller;
	$this->_escaper = $escaper;
	$this->customerRepository = $customerRepository;
	}

	/**
	* Post user question
	*
	* @return void
	* @throws \Exception
	*/
	public function execute()
	{
		$shortDate = "-1 Day";
        $increaseDate = strtotime($shortDate);
        $planExpiryDate = date("Y-m-d", $increaseDate);

        $today = "today";
        $todayDate = strtotime($today);
        $plantodayDate = date("Y-m-d", $todayDate);
		
		$orderCollection = $this->_MembershipOrders->create()->getCollection();
        $orderCollection->addFieldToFilter('order_status', 'complete');
        $orderCollection->addFieldToFilter('plan_expiry_date', array('from' => $planExpiryDate,'to' => $plantodayDate,'date' => true));
        $orderCollection->addFieldToFilter('plan_expiry_status', 0);
		
		$orders = $orderCollection->getData();
		foreach ($orders as $key => $order) {
			$customer_id = $order['customer_id'];
			$customer = $this->customerRepository->getById($customer_id);
			$sellercoll = $this->_seller->getCollection();	
			$sellercoll->addFieldToFilter('customer_id',$order['customer_id'])
			->addFieldToFilter('status',1);
			if(count($sellercoll) > 0){
                $group_id = 4;
			}else{
				$group_id = 1;
			}
			$customer->setGroupId($group_id);
            $this->customerRepository->save($customer);
		}
		return $this;
	}
}