<?php

namespace Magemonkey\Recurring\Controller\Recurring;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	/**
	* Recipient email config path
	*/
	const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';
	/**
	* @var \Magento\Framework\Mail\Template\TransportBuilder
	*/
	protected $_transportBuilder;

	/**
	* @var \Magento\Framework\Translate\Inline\StateInterface
	*/
	protected $inlineTranslation;

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
	\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
	\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magedelight\MembershipSubscription\Model\MembershipOrders $MembershipOrders,
	\Magedelight\MembershipSubscription\Model\MembershipProducts $MembershipProducts,
	\Magento\Sales\Model\Order $order,
	\Magento\Framework\Escaper $escaper
	) {
	parent::__construct($context);
	$this->scopeConfig = $scopeConfig;
	$this->storeManager = $storeManager;
	$this->_MembershipOrders = $MembershipOrders;
	$this->_MembershipProducts = $MembershipProducts;
	$this->_order = $order;
	$this->_escaper = $escaper;
	}

	/**
	* Post user question
	*
	* @return void
	* @throws \Exception
	*/
	public function execute()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('recurring_order');
		$baseUrl = $this->storeManager->getStore()->getBaseUrl();

		$order_id = $_GET['order_id'];
		
		// echo "<pre>";
		$memorder = $this->_MembershipOrders->load($order_id, 'order_id');
		$order = $this->_order->load($order_id);
		// print_r($memorder->getData());
		// exit();
		$today = strtotime($memorder->getPlanExpiryDate());
		$plandate = date("Y-m-d",$today);
		$sql = "SELECT * FROM " . $tableName . " WHERE `next_date` = '".$plandate."' AND `execute` = 0 AND `order_id` = ".$order_id;
		$result = $connection->fetchAll($sql);
		/*echo "<pre>";
		print_r($result);
		exit();*/
		
		if(count($memorder) > 0 && count($result) == 0){
			$customerPlanArray = unserialize($memorder->getCustomerPlan());
			$duration = trim($customerPlanArray['duration']);
			$duration_unit = $customerPlanArray['duration_unit'];
			/*echo $memorder->getPlanExpiryDate();
			echo "<br>";
			exit();*/

			$start_week = date("m-d",$today);
			for ($i = 0; $i < $duration; $i++) 
			{
			    $order_id = $memorder->getOrderId();
			    $customer_id = $memorder->getCustomerId();
			    $increment_id = $order->getIncrementId();
			    $customer_email = $memorder->getCustomerEmail();
			    if($duration_unit == "Year"){
			    	$date = date("Y-m-d", strtotime( date( 'Y-'.$start_week )." +1 year"));
			    }else{
			    	$date = date("Y-m-d", strtotime( date( 'Y-'.$start_week )." +$i months"));
			    }
			    
			    $price = $memorder->getPrice();
			    $sql = "INSERT INTO " . $tableName . "(order_id,increment_id,customer_id,customer_email,next_date,price) VALUES (".$order_id.",'".$increment_id."',".$customer_id.",'".$customer_email."','".$date."',".$price.")";
			    // echo $sql."<br>";
			    $connection->query($sql);
			}
			$data = ['plan_expiry_date'=>$date];
            if (count($data)>0) {
            	$memorder->addData($data);
            	$memorder->save();
        	}
			$this->messageManager->addSuccess(__('Thanks for renew the membership plan.'));
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
           	$resultRedirect->setUrl($baseUrl);
           	return $resultRedirect;
		}else{
			$this->messageManager->addError(__('Membership plan has been not found or you have already renew the membership plan.'));
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
           	$resultRedirect->setUrl($baseUrl);
           	return $resultRedirect;
		}
		
	}

}