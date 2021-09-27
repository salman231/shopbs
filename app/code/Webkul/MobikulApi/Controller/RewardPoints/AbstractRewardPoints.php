<?php
namespace Webkul\MobikulApi\Controller\RewardPoints;

abstract class AbstractRewardPoints extends \Webkul\MobikulApi\Controller\ApiController
{
	protected $_storeManager;
    protected $_customerRepositoryInterface;
    protected $quoteFactory;
    protected $quoteValidator;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $jsonHelper;
    protected $checkoutSession;
    protected $transactionCollectionFactory;
    protected $transactionRepository;
    protected $searchCriteriaBuilder;
    protected $_rewardsPurchase;
    protected $rewardsData;
    protected $rewardsConfig;
    protected $taxConfig;
    protected $cartFactory;
	public function __construct(
	   \Magento\Store\Model\StoreManagerInterface $storeManager,
       \Psr\Log\LoggerInterface $logger,
       \Magento\Store\Model\App\Emulation $emulate,
       \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
       \Magento\Checkout\Model\Session $checkoutSession,
       \Magento\Quote\Model\QuoteFactory $quoteFactory,
       \Magento\Quote\Model\QuoteValidator $quoteValidator,
       \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
       \Magento\Quote\Model\QuoteManagement $quoteManagement,
       \Mirasvit\Rewards\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
       \Mirasvit\Rewards\Api\Repository\TransactionRepositoryInterface $transactionRepository,
       \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
       \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
       \Mirasvit\Rewards\Helper\Data $rewardsData,
       \Mirasvit\Rewards\Model\Config $rewardsConfig,
       \Magento\Tax\Model\Config $taxConfig,
       \Magento\Checkout\Model\CartFactory $cartFactory, 
       \Webkul\MobikulCore\Helper\Data $helper,\Magento\Framework\App\Action\Context $context,
       \Magento\Framework\Json\Helper\Data $jsonHelper
    )
	{
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
        $this->emulate = $emulate;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->quoteValidator = $quoteValidator;
        $this->quoteManagement = $quoteManagement;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_rewardsPurchase = $rewardsPurchase;
        $this->jsonHelper = $jsonHelper;
        $this->rewardsData = $rewardsData;
        $this->rewardsConfig = $rewardsConfig;
        $this->taxConfig = $taxConfig;
        $this->cartFactory = $cartFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        parent::__construct($helper, $context, $jsonHelper);
	}
}