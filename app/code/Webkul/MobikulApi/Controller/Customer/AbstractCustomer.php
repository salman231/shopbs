<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Customer;

abstract class AbstractCustomer extends \Webkul\MobikulApi\Controller\ApiController
{
    protected $cart;
    protected $checkoutSession;
    protected $vote;
    protected $order;
    protected $quote;
    protected $review;
    protected $visitor;
    protected $country;
    protected $baseDir;
    protected $wishlist;
    protected $request;
    protected $userImage;
    protected $encryptor;
    protected $jsonHelper;
    protected $localeDate;
    protected $orderTotals;
    protected $salesHelper;
    protected $cartFactory;
    protected $tokenHelper;
    protected $compareItem;
    protected $listProduct;
    protected $orderConfig;
    protected $quoteFactory;
    protected $storeManager;
    protected $wishlistItem;
    protected $shippingView;
    protected $reviewHelper;
    protected $customerList;
    protected $customerForm;
    protected $reviewFactory;
    protected $stockRegistry;
    protected $catalogConfig;
    protected $addressHelper;
    protected $purchasedlink;
    protected $invoiceTotals;
    protected $ratingFactory;
    protected $priceRenderer;
    protected $productHelper;
    protected $pricingHelper;
    protected $securityConfig;
    protected $authentication;
    protected $productFactory;
    protected $customerMapper;
    protected $wishlistHelper;
    protected $orderInfoBlock;
    protected $voteCollection;
    protected $stockRepository;
    protected $customerSession;
    protected $customerAddress;
    protected $orderCollection;
    protected $customerFactory;
    protected $wishlistProvider;
    protected $transportBuilder;
    protected $creditmemoTotals;
    protected $reviewCollection;
    protected $downloadableLink;
    protected $dataObjectHelper;
    protected $inlineTranslation;
    protected $purchasedLinkItem;
    protected $productVisibility;
    protected $productRepository;
    protected $accountManagement;
    protected $emailNotification;
    protected $customerExtractor;
    protected $addressRepository;
    protected $countryCollection;
    protected $orderHistoryBlock;
    protected $orderItemRenderer;
    protected $collectionFactory;
    protected $wishlistItemOption;
    protected $downloadableHelper;
    protected $invoiceItemRenderer;
    protected $downloadableFileHelper;
    protected $purchasedLinkCollection;
    protected $productCollectionFactory;
    protected $catalogProductCompareList;
    protected $productConfigurationHelper;
    protected $shipmentRepositoryInterface;
    protected $customerRepositoryInterface;
    protected $purchasedLinkItemCollection;
    protected $invoiceItemCollectionFactory;

    public function __construct(
        \Magento\Store\Model\Store $store,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Sales\Model\Order $order,
        \Magento\Checkout\Model\Cart $cart, // need to be removed.
        \Magento\Review\Model\Review $review,
        \Webkul\MobikulCore\Helper\Data $helper,
        \Magento\Customer\Model\Visitor $visitor,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Review\Helper\Data $reviewHelper,
        \Magento\Directory\Model\Country $country,
        \Magento\Wishlist\Model\Item $wishlistItem,
        \Magento\Customer\Model\Form $customerForm,
        \Magento\Sales\Helper\Reorder $salesHelper,
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Webkul\MobikulCore\Helper\Token $tokenHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Webkul\MobikulCore\Model\UserImage $userImage,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Review\Model\Rating\Option\Vote $vote,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Sales\Block\Order\Info $orderInfoBlock,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Wishlist\Model\WishlistFactory $wishlist,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Wishlist\Model\Wishlist $wishlistProvider,
        \Magento\Downloadable\Model\Link $downloadableLink,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Sales\Block\Order\History $orderHistoryBlock,
        \Magento\Downloadable\Helper\Data $downloadableHelper,
        \Magento\Weee\Block\Item\Price\Renderer $priceRenderer,
        \Magento\Security\Model\ConfigInterface $securityConfig,
        \Magento\Catalog\Helper\Product\Compare $productCompare,
        \Magento\Wishlist\Model\Item\Option $wishlistItemOption,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Review\Block\Customer\View $customerReviewBlock,
        \Webkul\MobikulApi\Block\Sales\Order\Totals $orderTotals,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Block\Customer\ListCustomer $customerList,
        \Magento\Downloadable\Helper\File $downloadableFileHelper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Downloadable\Model\Link\Purchased $purchasedlink,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Customer\Model\CustomerExtractor $customerExtractor,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Downloadable\Model\Link\Purchased\Item $purchasedLinkItem,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Webkul\MobikulApi\Block\Sales\Order\Invoice\Totals $invoiceTotals,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepoInterface,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Shipping\Block\Adminhtml\Order\Tracking\View $shippingView,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $compareItem,
        \Magento\Weee\Block\Adminhtml\Items\Price\Renderer $adminPriceRenderer,
        \Magento\Downloadable\Block\Customer\Products\ListProducts $listProduct,
        \Webkul\MobikulApi\Block\Sales\Order\Creditmemo\Totals $creditmemoTotals,
        \Magento\Catalog\Helper\Product\Configuration $productConfigurationHelper,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepositoryInterface,
        \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $orderItemRenderer,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollection,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Review\Model\ResourceModel\Rating\Option\Vote\Collection $voteCollection,
        \Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer $invoiceItemRenderer,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection $purchasedLinkCollection,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory $invoiceItemCollectionFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection $purchasedLinkItemCollection,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $productCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Checkout\Model\Session $checkoutSession = null,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver
    ) {
        $this->checkoutSession = $checkoutSession == null ?
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Checkout\Model\Session::class
            ) : $checkoutSession;
        $this->cart = $cart;
        $this->vote = $vote;
        $this->store = $store;
        $this->quote = $quote;
        $this->order = $order;
        $this->helper = $helper;
        $this->review = $review;
        $this->emulate = $emulate;
        $this->visitor = $visitor;
        $this->request = $request;
        $this->country = $country;
        $this->baseDir = $dir->getPath("media");
        $this->wishlist = $wishlist;
        $this->userImage = $userImage;
        $this->encryptor = $encryptor;
        $this->localeDate = $localeDate;
        $this->jsonHelper = $jsonHelper;
        $this->tokenHelper = $tokenHelper;
        $this->salesHelper = $salesHelper;
        $this->orderTotals = $orderTotals;
        $this->cartFactory = $cartFactory;
        $this->compareItem = $compareItem;
        $this->orderConfig = $orderConfig;
        $this->listProduct = $listProduct;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->customerForm = $customerForm;
        $this->reviewHelper = $reviewHelper;
        $this->shippingView = $shippingView;
        $this->customerList = $customerList;
        $this->quoteFactory = $quoteFactory;
        $this->wishlistItem = $wishlistItem;
        $this->ratingFactory = $ratingFactory;
        $this->pricingHelper = $pricingHelper;
        $this->reviewFactory = $reviewFactory;
        $this->catalogConfig = $catalogConfig;
        $this->addressHelper = $addressHelper;
        $this->helperCatalog = $helperCatalog;
        $this->stockRegistry = $stockRegistry;
        $this->purchasedlink = $purchasedlink;
        $this->priceRenderer = $priceRenderer;
        $this->productHelper = $productHelper;
        $this->invoiceTotals = $invoiceTotals;
        $this->securityConfig = $securityConfig;
        $this->productFactory = $productFactory;
        $this->orderInfoBlock = $orderInfoBlock;
        $this->wishlistHelper = $wishlistHelper;
        $this->voteCollection = $voteCollection;
        $this->productCompare = $productCompare;
        $this->customerAddress = $customerAddress;
        $this->stockRepository = $stockRepository;
        $this->orderCollection = $orderCollection;
        $this->customerFactory = $customerFactory;
        $this->customerSession = $customerSession;
        $this->downloadableLink = $downloadableLink;
        $this->wishlistProvider = $wishlistProvider;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->reviewCollection = $reviewCollection;
        $this->creditmemoTotals = $creditmemoTotals;
        $this->transportBuilder = $transportBuilder;
        $this->purchasedLinkItem = $purchasedLinkItem;
        $this->collectionFactory = $collectionFactory;
        $this->productVisibility = $productVisibility;
        $this->productRepository = $productRepository;
        $this->accountManagement = $accountManagement;
        $this->inlineTranslation = $inlineTranslation;
        $this->addressRepository = $addressRepository;
        $this->customerExtractor = $customerExtractor;
        $this->countryCollection = $countryCollection;
        $this->invoiceRepository = $invoiceRepoInterface;
        $this->orderHistoryBlock = $orderHistoryBlock;
        $this->orderItemRenderer = $orderItemRenderer;
        $this->downloadableHelper = $downloadableHelper;
        $this->wishlistItemOption = $wishlistItemOption;
        $this->adminPriceRenderer = $adminPriceRenderer;
        $this->invoiceItemRenderer = $invoiceItemRenderer;
        $this->customerReviewBlock = $customerReviewBlock;
        $this->downloadableFileHelper = $downloadableFileHelper;
        $this->purchasedLinkCollection = $purchasedLinkCollection;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductCompareList = $catalogProductCompareList;
        $this->productConfigurationHelper = $productConfigurationHelper;
        $this->shipmentRepositoryInterface = $shipmentRepositoryInterface;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->purchasedLinkItemCollection = $purchasedLinkItemCollection;
        $this->invoiceItemCollectionFactory = $invoiceItemCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->customerMapper = $customerMapper;
        $this->fileDriver = $fileDriver;
        parent::__construct($helper, $context, $jsonHelper);
    }
}
