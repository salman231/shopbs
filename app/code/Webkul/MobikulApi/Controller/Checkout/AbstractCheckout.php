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

namespace Webkul\MobikulApi\Controller\Checkout;

use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\MobikulCore\Helper\Data as HelperData;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Quote\Model\Quote\Address\ToOrder as ToOrderConverter;
use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ToOrderAddressConverter;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Abstract Class Abstract Checkout
 */
abstract class AbstractCheckout extends \Webkul\MobikulApi\Controller\ApiController
{
    protected $store;
    protected $ccType;
    protected $country;
    protected $emulate;
    protected $escaper;
    protected $wishlist;
    protected $jsonHelper;
    protected $stockState;
    protected $orderSender;
    protected $priceHelper;
    protected $deviceToken;
    protected $cartFactory;
    protected $customerUrl;
    protected $catalogLink;
    protected $salesHelper;
    protected $orderFactory;
    protected $quoteFactory;
    protected $coreRegistry;
    protected $eventManager;
    protected $storeManager;
    protected $customerForm;
    protected $paypalConfig;
    protected $mobikulOrder;
    protected $helperCatalog;
    protected $addressHelper;
    protected $invoiceSender;
    protected $productOption;
    protected $couponFactory;
    protected $dbTransaction;
    protected $paymentHelper;
    protected $paymentDetails;
    protected $checkoutHelper;
    protected $localeResolver;
    protected $productFactory;
    protected $quoteValidator;
    protected $invoiceService;
    protected $relatedProducts;
    protected $customerFactory;
    protected $customerAddress;
    protected $quoteRepository;
    protected $checkoutSession;
    protected $quoteManagement;
    protected $addressInterface;
    protected $regionCollection;
    protected $dataObjectHelper;
    protected $orderEmailSender;
    protected $requestInfoFilter;
    protected $accountManagement;
    protected $countryCollection;
    protected $objectCopyService;
    protected $productVisibility;
    protected $customerRepository;
    protected $transactionBuilder;
    protected $orderCustomermanager;
    protected $paymentMethodInterface;
    protected $shippingMethodManagement;
    protected $downloadableConfiguration;
    protected $catalogHelper;
    protected $productRepository;
    protected $remoteAddress;
    protected $orderPurchaseFactory;
    protected $_MembershipOrdersFactory;
    protected $_order;
    protected $_MembershipProductsFactory;
    protected $serializer;
    protected $_productRepository;
    protected $_giftUserFactory;
    public function __construct(
        Context $context,
        Emulation $emulate,
        HelperData $helper,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\Escaper $escaper,
        ToOrderConverter $quoteAddressToOrder,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Directory\Model\Country $country,
        \Magento\Framework\Registry $coreRegistry,
        ToOrderItemConverter $quoteItemToOrderItem,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Sales\Helper\Reorder $salesHelper,
        \Magento\Customer\Model\Form $customerForm,
        \Magento\Paypal\Model\Config $paypalConfig,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Source\Cctype $ccType,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        AccountManagementInterface $accountManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Helper\Address $addressHelper,
        Transaction\BuilderInterface $transactionBuilder,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Magento\Customer\Model\Address $customerAddress,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Webkul\MobikulCore\Model\DeviceToken $deviceToken,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        ToOrderAddressConverter $quoteAddressToOrderAddress,
        ToOrderPaymentConverter $quotePaymentToOrderPayment,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Catalog\Model\Product\Option $productOption,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Checkout\Model\PaymentDetails $paymentDetails,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Product\LinkFactory $catalogLink,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Webkul\MobikulCore\Model\SalesOrderFactory $mobikulOrder,
        \Magento\Quote\Api\Data\AddressInterface $addressInterface,
        \Magento\Quote\Model\CustomerManagement $customerManagement,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Model\Quote\Item\RelatedProducts $relatedProducts,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderEmailSender,
        \Magento\Quote\Model\ShippingMethodManagement $shippingMethodManagement,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomermanager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyInterface,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodInterface,
        \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollection,
        \Magento\Downloadable\Helper\Catalog\Product\Configuration $downloadableConfiguration,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Helper\Data $catalogHelper = null,
        ProductRepositoryInterface $productRepository,
        RemoteAddress $remoteAddress,
        \Webkul\MobikulCore\Model\OrderPurchasePointFactory $orderPurchaseFactory,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magedelight\MembershipSubscription\Model\MembershipOrdersFactory $MembershipOrdersFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magedelight\MembershipSubscription\Model\MembershipProductsFactory $MembershipProductsFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Catalog\Model\ProductRepository $productRepositorye,
        \Webkul\GiftCard\Model\GiftUserFactory $giftUserFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64Json
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->catalogHelper = ($catalogHelper) ? $catalogHelper : $objectManager->get(
            \Magento\Catalog\Helper\Data::class
        );
        $this->curl = $curl;
        $this->store = $store;
        $this->ccType = $ccType;
        $this->country = $country;
        $this->emulate = $emulate;
        $this->escaper = $escaper;
        $this->wishlist = $wishlist;
        $this->resource = $resource;
        $this->stockState = $stockState;
        $this->jsonHelper = $jsonHelper;
        $this->salesHelper = $salesHelper;
        $this->catalogLink = $catalogLink;
        $this->priceHelper = $priceHelper;
        $this->cartFactory = $cartFactory;
        $this->customerUrl = $customerUrl;
        $this->deviceToken = $deviceToken;
        $this->orderSender = $orderSender;
        $this->mobikulOrder = $mobikulOrder;
        $this->paypalConfig = $paypalConfig;
        $this->eventManager = $eventManager;
        $this->customerForm = $customerForm;
        $this->coreRegistry = $coreRegistry;
        $this->orderFactory = $orderFactory;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        $this->paymentHelper = $paymentHelper;
        $this->productOption = $productOption;
        $this->addressHelper = $addressHelper;
        $this->helperCatalog = $helperCatalog;
        $this->couponFactory = $couponFactory;
        $this->invoiceSender = $invoiceSender;
        $this->stockRegistry = $stockRegistry;
        $this->dbTransaction = $dbTransaction;
        $this->checkoutHelper = $checkoutHelper;
        $this->quoteValidator = $quoteValidator;
        $this->localeResolver = $localeResolver;
        $this->productFactory = $productFactory;
        $this->invoiceService = $invoiceService;
        $this->paymentDetails = $paymentDetails;
        $this->customerAddress = $customerAddress;
        $this->quoteManagement = $quoteManagement;
        $this->relatedProducts = $relatedProducts;
        $this->quoteRepository = $quoteRepository;
        $this->customerFactory = $customerFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->addressInterface = $addressInterface;
        $this->orderEmailSender = $orderEmailSender;
        $this->regionCollection = $regionCollection;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountManagement = $accountManagement;
        $this->productVisibility = $productVisibility;
        $this->objectCopyService = $objectCopyService;
        $this->countryCollection = $countryCollection;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->customerRepository = $customerRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->customerManagement = $customerManagement;
        $this->quoteAddressToOrder = $quoteAddressToOrder;
        $this->orderCustomermanager = $orderCustomermanager;
        $this->quoteItemToOrderItem = $quoteItemToOrderItem;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->paymentMethodInterface = $paymentMethodInterface;
        $this->priceCurrencyInterface = $priceCurrencyInterface;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->downloadableConfiguration = $downloadableConfiguration;
        $this->quotePaymentToOrderPayment = $quotePaymentToOrderPayment;
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
        $this->productRepository = $productRepository;
        $this->remoteAddress = $remoteAddress;
        $this->orderPurchaseFactory = $orderPurchaseFactory;
        $this->_MembershipOrdersFactory = $MembershipOrdersFactory;
        $this->_order = $order;
        $this->_MembershipProductsFactory = $MembershipProductsFactory;
        $this->serializer = $serializer;
        $this->_productRepository = $productRepositorye;
        $this->_giftUserFactory = $giftUserFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->fileDriver = $fileDriver;
        $this->base64Json = $base64Json;
        parent::__construct($helper, $context, $jsonHelper);
    }

    /**
     * Function to check if customerEmail Exist
     *
     * @param string $email     email
     * @param int    $websiteId websiteId
     *
     * @return bool
     */
    protected function _customerEmailExists($email, $websiteId = null)
    {
        $customer = $this->customerFactory->create();
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }
        return false;
    }

    /**
     * Function to validate customer Data
     *
     * @param array $data data
     *
     * @return bool|array
     */
    protected function _validateCustomerData($data)
    {
        $storeId = $data["storeId"];
        $customerData = [];
        $customer = null;
        $customerForm = $this->customerForm->setFormCode("checkout_register");
        $quote = new \Magento\Framework\DataObject();
        $customerToken = $data["customerToken"] ?? "";
        $customerId = $this->helper->getCustomerByToken($customerToken) ?? 0;
        if ($customerId != 0) {
            $quote = $this->helper->getCustomerQuote($customerId);
        }
        if (isset($data["quoteId"]) && $data["quoteId"] != 0) {
            $quoteId = $data["quoteId"];
            $quote = $this->quoteFactory->create()->setStoreId($storeId)->load($quoteId);
        }
        if ($quote->getCustomerId()) {
            $customer = $quote->getCustomer();
            $customer = $this->customerFactory->create()->load($customer->getId());
            $customerForm->setEntity($customer);
            $customerData = $customer->getData();
        } else {
            $customer = $this->customerFactory->create();
            $customerForm->setEntity($customer);
            $newAddress = [];
            if (isset($data["billingData"])) {
                $billingData = $data["billingData"];
            } else {
                $billingData = $data["shippingData"];
            }
            $billingData = $this->jsonHelper->jsonDecode($billingData);
            if (isset($billingData["newAddress"])) {
                if (!empty($billingData["newAddress"])) {
                    $newAddress = $billingData["newAddress"];
                }
            }
            $customerData = [
                "lastname" => $newAddress["lastName"],
                "firstname" => $newAddress["firstName"],
                "dob" => $newAddress["dob"] ?? "",
                "email" => $newAddress["email"] ?? "",
                "prefix" => $newAddress["prefix"] ?? "",
                "suffix" => $newAddress["suffix"] ?? "",
                "taxvat" => $newAddress["taxvat"] ?? "",
                "gender" => $newAddress["gender"] ?? "",
                "middlename" => $newAddress["middleName"] ?? ""
            ];
        }
        $customerErrors = true;
        if ($customerErrors !== true) {
            return ["error"=>1, "message"=>implode(", ", $customerErrors)];
        }
        if ($quote->getCustomerId()) {
            return true;
        }
        if ($quote->getCheckoutMethod() == "register") {
            $customerForm->compactData($customerData);
            $customer->setPassword($data["password"]);
            $customer->setConfirmation($data["confirmPassword"]);
            $customer->setPasswordConfirmation($data["confirmPassword"]);
            $result = $customer->validate();
            if (true !== $result && is_array($result)) {
                return ["error"=>-1, "message"=>implode(", ", $result)];
            }
        }
        if ($quote->getCheckoutMethod() == "register") {
            $quote->setPasswordHash($customer->encryptPassword($customer->getPassword()));
            $quote->setCustomer($customer);
        }
        $quote->getBillingAddress()->setEmail($customer->getEmail());
        $this->objectCopyService->copyFieldsetToTarget("customer_account", "to_quote", $customer, $quote);
        return true;
    }
}
