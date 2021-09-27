<?php

namespace Magemonkey\Recurring\Controller\Sendmail;

use Magento\Framework\App\RequestInterface;

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
	\Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory,
	\Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory,
	\Magento\Sales\Model\Order $order,
	\Magento\Framework\Escaper $escaper
	) {
	parent::__construct($context);
	$this->_transportBuilder = $transportBuilder;
	$this->inlineTranslation = $inlineTranslation;
	$this->scopeConfig = $scopeConfig;
	$this->storeManager = $storeManager;
	$this->_MembershipOrdersFactory = $MembershipOrdersFactory;
	$this->_MembershipProductsFactory = $MembershipProductsFactory;
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
		// $duration = (int)$this->scopeConfig->getValue('membership/general/mail_before_days', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$duration = 5;
		// echo $duration;
		$planExpiryDate = '';
        if ($duration) {
            $shortDate = "+".$duration." Day";
            $increaseDate = strtotime($shortDate);
            $planExpiryDate = date("Y-m-d", $increaseDate);
			//$planExpiryDate = "2017-10-04";
        }
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 

		$email = $this->scopeConfig->getValue('trans_email/ident_sales/email',$storeScope);
    	$name  = $this->scopeConfig->getValue('trans_email/ident_sales/name',$storeScope);

        /*echo $email." ".$name;
        exit();*/
        $orderCollection = $this->_MembershipOrdersFactory->create()->getCollection();
        $orderCollection->addFieldToFilter('order_status', 'complete');
        $orderCollection->addFieldToFilter('plan_expiry_date', ['lteq' => $planExpiryDate]);
        $orderCollection->addFieldToFilter('plan_expiry_status', 0);

		//echo $orderCollection->getSelect()->__toString();
		// exit;

        $orders = $orderCollection->getData();
        /*echo "<pre>";
        print_r($orders);
        exit();*/
        if (count($orders)>0) {
	        $this->inlineTranslation->suspend();
		    try {
		    	$baseUrl = $this->storeManager->getStore()->getBaseUrl();
		    	foreach ($orders as $key => $value) {

			    	$post['name'] = $this->getProductName($value['product_id']);
			    	$post['expierdate'] = $value['plan_expiry_date'];
			        $post['renewurl'] =  $baseUrl."magemonkey/recurring?order_id=".$value['order_id'];

			        $postObject = new \Magento\Framework\DataObject();
			        $postObject->setData($post);
			        $error = false;
			        $sender = [
			            'name' => $this->_escaper->escapeHtml($name),
			            'email' => $this->_escaper->escapeHtml($email),
			        ];
			        $transport = $this->_transportBuilder
			            ->setTemplateIdentifier(26) // this code we have mentioned in the email_templates.xml or template id from admin
			            ->setTemplateOptions(
			                [
			                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND, 
			                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
			                ]
			            )
			            ->setTemplateVars(['data' => $postObject])
			            ->setFrom($sender)
			            ->addTo($value['customer_email'])
			            ->getTransport();

		            $transport->sendMessage();
		            $this->inlineTranslation->resume();
		            $this->messageManager->addSuccess(
		                __('Thanks for renew the membership plan.')
		            );
		            
		            
		        }
		        exit();
		        return $this;
		    } catch (\Exception $e) {
		        $this->inlineTranslation->resume();
		        $this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
		        );
		        exit();
		        return $this;
		    }

		}else{
			$this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'));
			exit();
		    return $this;
		}
		/*exit();	
		$post = $this->getRequest()->getPostValue();
		if (!$post) {
		$this->_redirect('/');
		return;
		}*/

		
	}


    /**
     *
     * @param type $productId
     * @return type
     */
    public function getProductName($productId)
    {
        $model = $this->_MembershipProductsFactory->create();
        $model->load($productId, 'product_id');
        return $model->getProductName();
    }
   
    /**
     *
     * @return string
     */
    public function getMembershipUrl()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $urlKey = trim($this->scopeConfig->getValue('membership/membership_settings/identifier', $storeScope));
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $url = $baseUrl."/".$urlKey;
        return $url;
    }
}