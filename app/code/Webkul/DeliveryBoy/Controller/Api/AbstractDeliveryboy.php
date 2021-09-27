<?php
/**
 * Webkul Software.
 *
 * PHP version 7.0+
 *
 * @category  Webkul
 * @package   Webkul_DeliveryBoy
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */
namespace Webkul\DeliveryBoy\Controller\Api;

abstract class AbstractDeliveryboy extends \Webkul\DeliveryBoy\Controller\Api\ApiController
{
    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy
     */
    protected $deliveryboy;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Webkul\DeliveryBoy\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Webkul\DeliveryBoy\Model\RatingFactory
     */
    protected $ratingFactory;

    /**
     * @var \Magento\Weee\Block\Item\Price\Renderer
     */
    protected $priceRenderer;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Catalog
     */
    protected $helperCatalog;

    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $orderConverter;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Sales\Block\Order\Info
     */
    protected $orderInfoBlock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollection;

    /**
     * @var \Magento\Framework\Intl\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $websiteManager;

    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    protected $shipmentNotifier;

    /**
     * @var \Webkul\DeliveryBoy\Model\OrderFactory
     */
    protected $deliveryboyOrder;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $ratingCollection;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $deliveryboyHelper;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
     */
    protected $orderItemRenderer;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Comment\CollectionFactory
     */
    protected $commentCollection;

    /**
     * @var \Webkul\DeliveryBoy\Model\CommentFactory
     */
    protected $deliveryboyComment;

    /**
     * @var \Magento\Sales\Model\Order\Status
     */
    protected $orderStatusCollection;

    /**
     * @var \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface
     */
    protected $deliveryboyRepository;

    /**
     * @var \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterfaceFactory
     */
    protected $deliveryboyDataFactory;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection
     */
    protected $tokenResourceCollection;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     */
    protected $deliveryboyResourceCollection;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderResourceCollection;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Operation
     */
    protected $operationHelper;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $fileDriver;

    /**
     * @param \Magento\Store\Model\App\Emulation $emulate
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Block\Order\Info $orderInfoBlock
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Webkul\DeliveryBoy\Helper\Catalog $helperCatalog
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Sales\Model\Convert\Order $orderConverter
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Webkul\DeliveryBoy\Model\TokenFactory $tokenFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteManager
     * @param \Webkul\DeliveryBoy\Model\RatingFactory $ratingFactory
     * @param \Magento\Weee\Block\Item\Price\Renderer $priceRenderer
     * @param \Webkul\DeliveryBoy\Block\Sales\Order\Totals $orderTotals
     * @param \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrder
     * @param \Magento\Sales\Model\Order\Status $orderStatusCollection
     * @param \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Webkul\DeliveryBoy\Model\CommentFactory $deliveryboyComment
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
     * @param \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $orderItemRenderer
     * @param \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryboyRepository
     * @param \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterfaceFactory $deliveryboyDataFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenResourceCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $ratingCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Comment\CollectionFactory $commentCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollection
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyResourceCollection
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\DeliveryBoy\Helper\Operation $operationHelper
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Webkul\DeliveryBoy\Helper\Authentication $authHelper
     */
    public function __construct(
        \Magento\Store\Model\App\Emulation $emulate,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Block\Order\Info $orderInfoBlock,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Webkul\DeliveryBoy\Helper\Catalog $helperCatalog,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\Convert\Order $orderConverter,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\DeliveryBoy\Model\TokenFactory $tokenFactory,
        \Magento\Store\Model\WebsiteFactory $websiteManager,
        \Webkul\DeliveryBoy\Model\RatingFactory $ratingFactory,
        \Magento\Weee\Block\Item\Price\Renderer $priceRenderer,
        \Webkul\DeliveryBoy\Block\Sales\Order\Totals $orderTotals,
        \Magento\Framework\Intl\DateTimeFactory $dateTimeFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\DeliveryBoy\Model\OrderFactory $deliveryboyOrder,
        \Magento\Sales\Model\Order\Status $orderStatusCollection,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Webkul\DeliveryBoy\Model\CommentFactory $deliveryboyComment,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $orderItemRenderer,
        \Webkul\DeliveryBoy\Api\DeliveryboyRepositoryInterface $deliveryboyRepository,
        \Webkul\DeliveryBoy\Api\Data\DeliveryboyInterfaceFactory $deliveryboyDataFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Token\Collection $tokenResourceCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Rating\CollectionFactory $ratingCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Comment\CollectionFactory $commentCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollection,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyResourceCollection,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\DeliveryBoy\Helper\Operation $operationHelper,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Webkul\DeliveryBoy\Helper\Authentication $authHelper
    ) {
        parent::__construct($context, $authHelper, $jsonHelper);

        $this->date = $date;
        $this->config = $config;
        $this->websiteManager = $websiteManager;
        $this->emulate = $emulate;
        $this->resource = $resource;
        $this->timezone = $timezone;
        $this->encryptor = $encryptor;
        $this->jsonHelper = $jsonHelper;
        $this->mathRandom = $mathRandom;
        $this->deliveryboy = $deliveryboy;
        $this->orderTotals = $orderTotals;
        $this->transaction = $transaction;
        $this->tokenFactory = $tokenFactory;
        $this->orderFactory = $orderFactory;
        $this->storeManager = $storeManager;
        $this->ratingFactory = $ratingFactory;
        $this->invoiceSender = $invoiceSender;
        $this->helperCatalog = $helperCatalog;
        $this->priceRenderer = $priceRenderer;
        $this->orderInfoBlock = $orderInfoBlock;
        $this->invoiceService = $invoiceService;
        $this->orderConverter = $orderConverter;
        $this->priceFormatter = $priceFormatter;
        $this->customerFactory = $customerFactory;
        $this->orderRepository = $orderRepository;
        $this->orderCollection = $orderCollection;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->deliveryboyOrder = $deliveryboyOrder;
        $this->transportBuilder = $transportBuilder;
        $this->ratingCollection = $ratingCollection;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->inlineTranslation = $inlineTranslation;
        $this->commentCollection = $commentCollection;
        $this->deliveryboyHelper = $deliveryboyHelper;
        $this->orderItemRenderer = $orderItemRenderer;
        $this->deliveryboyComment = $deliveryboyComment;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->orderStatusCollection = $orderStatusCollection;
        $this->deliveryboyRepository = $deliveryboyRepository;
        $this->deliveryboyDataFactory = $deliveryboyDataFactory;
        $this->tokenResourceCollection = $tokenResourceCollection;
        $this->deliveryboyResourceCollection = $deliveryboyResourceCollection;
        $this->deliveryboyOrderResourceCollection = $deliveryboyOrderResourceCollection;
        $this->jsonHelper = $jsonHelper;
        $this->fileDriver = $fileDriver;
        try {
            $this->baseDir = $dir->getPath("media");
            $this->mediaDirectory = $filesystem->getDirectoryWrite(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            );
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $this->baseDir = '';
            $this->mediaDirectory = null;
        }
        $this->logger = $logger;
        $this->operationHelper = $operationHelper;
        $this->fileDriver = $fileDriver;

        parent::__construct($context, $authHelper, $jsonHelper);
    }
}
