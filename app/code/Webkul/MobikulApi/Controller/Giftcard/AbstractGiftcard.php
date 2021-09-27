<?php
namespace Webkul\MobikulApi\Controller\Giftcard;

abstract class AbstractGiftcard extends \Webkul\MobikulApi\Controller\ApiController
{
	protected $_storeManager;
	protected $_giftuser;
	protected $_giftDetail;
	protected $_dataHelper;
	protected $_salesRule;
    protected $_customerRepositoryInterface;
    //protected $_checkoutSession;
    protected $quoteFactory;
    protected $quoteValidator;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $jsonHelper;
    protected $checkoutSession;
	public function __construct(
	   //\Magento\Catalog\Block\Product\Context $productcontext,
       \Magento\Store\Model\StoreManagerInterface $storeManager,
       \Webkul\GiftCard\Model\GiftUserFactory $giftUser,
       \Webkul\GiftCard\Model\GiftDetailFactory $giftDetail,
       \Webkul\GiftCard\Helper\Data $dataHelper,
       \Magento\SalesRule\Model\Rule $salesRule,
       \Psr\Log\LoggerInterface $logger,
       \Magento\Store\Model\App\Emulation $emulate,
       \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
       \Magento\Checkout\Model\Session $checkoutSession,
       \Magento\Quote\Model\QuoteFactory $quoteFactory,
       \Magento\Quote\Model\QuoteValidator $quoteValidator,
       \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
       \Magento\Quote\Model\QuoteManagement $quoteManagement,
       \Webkul\MobikulCore\Helper\Data $helper,\Magento\Framework\App\Action\Context $context,
       \Magento\Framework\Json\Helper\Data $jsonHelper,
       \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory
	)
	{
        $this->_storeManager = $storeManager;
        $this->_giftuser = $giftUser;
        $this->_giftDetail = $giftDetail;
        $this->_dataHelper = $dataHelper;
        $this->_salesRule = $salesRule;
        $this->logger = $logger;
        $this->emulate = $emulate;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->quoteValidator = $quoteValidator;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->itemCollectionFactory = $itemCollectionFactory;
        //$this->scopeConfig = $productcontext->getScopeConfig();
        parent::__construct($helper, $context, $jsonHelper);
	}
}