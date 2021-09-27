<?php
namespace Webkul\MobikulApi\Controller\Membership;

abstract class AbstractMembership extends \Webkul\MobikulApi\Controller\ApiController
{
	protected $_MembershipProductsFactory;
	protected $_MembershipOrdersFactory;
	protected $_productloader;
	protected $_priceCurrency;
	protected $_customerGroup;
	protected $scopeConfig;
   protected $_customerRepositoryInterface;
   protected $jsonHelper;
   protected $_order;
   protected $_invoice;
   protected $_creditmemoFactory;
   protected $_creditmemoService;
   protected $_customerRepository;
   protected $_seller;
   protected $_urlRewrite;
   protected $_scopeConfig;
	public function __construct(
	   //\Magento\Catalog\Block\Product\Context $productcontext,
      \Magento\Sales\Model\Order $order,
      \Magento\Sales\Model\Order\Invoice $invoice,
      \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
      \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
      \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
      \Webkul\Marketplace\Model\Seller $seller,
      \Webkul\MobikulCore\Helper\Data $helper,
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\Json\Helper\Data $jsonHelper,
      \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory,
      \Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory,
      \Magento\Catalog\Model\ProductFactory $_productloader,
      \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
      \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
      \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup,
      \Magento\Framework\Controller\ResultFactory $resultFactory,
      \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Store\Model\App\Emulation $emulate
	)
	{
      $this->_order= $order;
      $this->_invoice = $invoice;
      $this->_creditmemoFactory = $creditmemoFactory;
      $this->_creditmemoService = $creditmemoService;
      $this->_customerRepository = $customerRepository;
	$this->_MembershipProductsFactory = $MembershipProductsFactory;
      $this->_MembershipOrdersFactory = $MembershipOrdersFactory;
      $this->_seller = $seller;
      $this->_productloader = $_productloader;
      $this->_priceCurrency = $priceCurrency;
      $this->_customerGroup = $customerGroup;
      $this->_customerRepositoryInterface = $customerRepositoryInterface;
      $this->_resultFactory = $resultFactory;
      $this->helper = $helper;
      $this->jsonHelper = $jsonHelper;
      $this->_urlRewrite = $urlRewrite;
      $this->__scopeConfig = $scopeConfig;
      $this->emulate = $emulate;
      //$this->scopeConfig = $productcontext->getScopeConfig();
      parent::__construct($helper, $context, $jsonHelper);
	}
}