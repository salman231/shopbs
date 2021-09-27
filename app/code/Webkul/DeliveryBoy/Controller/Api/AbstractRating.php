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

abstract class AbstractRating extends \Webkul\DeliveryBoy\Controller\Api\AbstractDeliveryboy
{
    /**
     * @var \Webkul\DeliveryBoy\Api\RatingRepositoryInterface
     */
    protected $ratingRepository;

    /**
     * @var \Webkul\DeliveryBoy\Model\Rating\Ratingstatus
     */
    protected $ratingStatus;

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
     * @param \Webkul\DeliveryBoy\Api\RatingRepositoryInterface $ratingRepository
     * @param \Webkul\DeliveryBoy\Model\Rating\Ratingstatus $ratingStatus
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
        \Webkul\DeliveryBoy\Helper\Authentication $authHelper,
        \Webkul\DeliveryBoy\Api\RatingRepositoryInterface $ratingRepository,
        \Webkul\DeliveryBoy\Model\Rating\Ratingstatus $ratingStatus
    ) {
            parent::__construct(
                $emulate,
                $config,
                $filesystem,
                $mathRandom,
                $transaction,
                $context,
                $jsonHelper,
                $orderFactory,
                $orderInfoBlock,
                $dir,
                $helperCatalog,
                $date,
                $orderConverter,
                $deliveryboyHelper,
                $deliveryboy,
                $resource,
                $tokenFactory,
                $websiteManager,
                $ratingFactory,
                $priceRenderer,
                $orderTotals,
                $dateTimeFactory,
                $customerFactory,
                $storeManager,
                $deliveryboyOrder,
                $orderStatusCollection,
                $shipmentNotifier,
                $encryptor,
                $invoiceService,
                $orderRepository,
                $deliveryboyComment,
                $timezone,
                $priceFormatter,
                $transportBuilder,
                $invoiceSender,
                $fileUploaderFactory,
                $inlineTranslation,
                $orderCollection,
                $orderItemRenderer,
                $deliveryboyRepository,
                $deliveryboyDataFactory,
                $tokenResourceCollection,
                $ratingCollection,
                $commentCollection,
                $deliveryboyOrderResourceCollection,
                $deliveryboyResourceCollection,
                $logger,
                $operationHelper,
                $fileDriver,
                $authHelper
            );
        $this->ratingRepository = $ratingRepository;
        $this->ratingStatus = $ratingStatus;
    }

    /**
     * @return array
     */
    protected function getRatingStatusValueArray(): array
    {
        $statusOptions = $this->ratingStatus->toOptionArray();
        return array_map(function ($status) {
            return $status['value'];
        }, $statusOptions);
    }
}
