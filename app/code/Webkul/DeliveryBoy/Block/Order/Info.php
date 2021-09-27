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
namespace Webkul\DeliveryBoy\Block\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Filesystem\DriverInterface as FileSystemDriver;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObject;

class Info extends \Magento\Framework\View\Element\Template
{
    const ALLOWED_SHIPPING = "deliveryboy/configuration/allowed_shipping";
    const DS = "/";

    /**
     * @var string
     */
    protected $_template = 'Webkul_DeliveryBoy::order/info.phtml';

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Webkul\DeliveryBoy\Model\Deliveryboy
     */
    protected $deliveryboy;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var FileSystemDriver
     */
    protected $fileSystemDriver;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param FileSystemDriver $fileSystemDriver
     * @param LoggerInterface $logger
     * @param \Webkul\DeliveryBoy\Model\Rating $rating
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderCollectionFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        \Webkul\DeliveryBoy\Helper\Data $helper,
        FileSystemDriver $fileSystemDriver,
        LoggerInterface $logger,
        \Webkul\DeliveryBoy\Model\Rating $rating,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Webkul\DeliveryBoy\Model\Deliveryboy $deliveryboy,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderCollectionFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->rating = $rating;
        $this->coreRegistry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        try {
            $this->baseDir = $dir->getPath("media");
        } catch (FileSystemException $e) {
            $this->baseDir = '';
            $this->logger->debug($e->getMessage());
        }
        $this->resource = $resource;
        $this->deliveryboy = $deliveryboy;
        $this->deliveryboyOrderCollectionFactory = $deliveryboyOrderCollectionFactory;
        $this->fileSystemDriver = $fileSystemDriver;
        $this->logger = $logger;
        $this->layoutFactory = $layoutFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @return bool
     */
    public function shouldShowSection()
    {
        $order = $this->getOrder();
        $allowedShipping = explode(
            ",",
            $this->scopeConfig->getValue(
                self::ALLOWED_SHIPPING
            )
        );
        $shippingMethod = $order->getShippingMethod();

        if (in_array($shippingMethod, $allowedShipping)) {
            $connection = $this->resource->getConnection();
            $tableName  = $this->resource->getTableName("deliveryboy_deliveryboy");
            $collection = $this->deliveryboyOrderCollectionFactory->create()->addFieldToFilter(
                "increment_id",
                $order->getIncrementId()
            );
            $collection->getSelect()
                ->join(
                    [
                        "deliveryboy" => $tableName
                    ],
                    "main_table.deliveryboy_id=deliveryboy.id",
                    [
                        "deliveryboy_name" => "deliveryboy.name",
                        "deliveryboy_email" => "deliveryboy.email",
                        "deliveryboy_avatar" => "deliveryboy.image",
                        "deliveryboy_vehicle_type" => "deliveryboy.vehicle_type",
                        "deliveryboy_mobile_number" => "deliveryboy.mobile_number",
                        "deliveryboy_vehicle_number" => "deliveryboy.vehicle_number",
                        "deliveryboy_order_id" => "main_table.id",
                        "deliveryboy_order_otp" => "main_table.otp",
                        "deliveryboy_order_picked" => "main_table.picked",
                    ]
                );
            $deliveryboyOrders = new DataObject();
            $deliveryboyOrders->setSize($collection->getSize());
            $deliveryboyOrdersArr = [];
            foreach ($collection as $deliveryboyOrder) {
                $deliveryboyOrderArray["deliveryboy_id"] = $deliveryboyOrder->getData("deliveryboy_id");
                $deliveryboyOrderArray["deliveryboy_order_id"]
                    = $deliveryboyOrder->getData("deliveryboy_order_id");
                $deliveryboyOrderArray["customer_id"] = $order->getCustomerId();
                $deliveryboyOrderArray["deliveryboy_avg_rating"] = (float)$this->rating->getAverageRating(
                    $deliveryboyOrderArray["deliveryboy_id"]
                );
                $deliveryboyOrderArray["deliveryboy_order_otp"]
                    = $deliveryboyOrder->getData("deliveryboy_order_otp");
                $deliveryboyOrderArray["deliveryboy_name"]
                    = $deliveryboyOrder->getData("deliveryboy_name");
                $deliveryboyOrderArray["deliveryboy_email"]
                    = $deliveryboyOrder->getData("deliveryboy_email");
                $deliveryboyOrderArray["deliveryboy_mobile"]
                    = $deliveryboyOrder->getData("deliveryboy_mobile_number");
                $deliveryboyOrderArray["deliveryboy_order_picked"]
                    = (bool)$deliveryboyOrder->getData("deliveryboy_order_picked");
                $deliveryboyOrderArray["deliveryboy_vehicle_number"]
                    = $deliveryboyOrder->getData("deliveryboy_vehicle_number");
                $Iconheight = $IconWidth = 144;
                $newUrl = "";
                $basePath = $this->baseDir . self::DS . $deliveryboyOrder->getData("deliveryboy_avatar");
                try {
                    if ($this->fileSystemDriver->isFile($basePath)) {
                        $newUrl = $this->helper->getUrl("media");
                        $newUrl .= self::DS . $deliveryboyOrder->getData("deliveryboy_avatar");
                    }
                } catch (FileSystemException $e) {
                    $this->logger->debug($e->getMessage());
                }
                $deliveryboyOrderArray["deliveryboy_avatar"] = $newUrl;
                $deliveryboyOrdersArr[] = new DataObject($deliveryboyOrderArray);
            }
            $deliveryboyOrders->setDeliveryboyOrders($deliveryboyOrdersArr);
            return $deliveryboyOrders;
        }
        return false;
    }

    /**
     * @return \Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactory()
    {
        return $this->layoutFactory;
    }

    /**
     * @return string
     */
    public function getGoogleMapKey()
    {
        return $this->helper->getGoogleMapKey();
    }
}
