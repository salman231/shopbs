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
namespace Webkul\DeliveryBoy\Block\Adminhtml\Edit\Account\Tab;

class DeliveryboyLocation extends \Magento\Backend\Block\Widget\Grid\Extended
{
     /**
     * Configuration path for google map api key
     */
    const CONFIGURATION_PATH_GOOGLE_MAP_KEY = 'deliveryboy/configuration/map_key';

    /**
     * @var string
     */
    protected $_template = "account/deliveryboy.phtml";

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderResourceCollectionFactory;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     */
    protected $deliveryboyResourceCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    private $deliveryboyHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     *        $deliveryboyResourceCollectionFactory
     * @param \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyResourceCollectionFactory,
        \Webkul\DeliveryBoy\Helper\Data $deliveryboyHelper,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->scopeConfig = $context->getScopeConfig();
        $this->deliveryboyResourceCollectionFactory = $deliveryboyResourceCollectionFactory;
        $this->deliveryboyOrderResourceCollectionFactory = $deliveryboyOrderResourceCollectionFactory;
        $this->deliveryboyHelper = $deliveryboyHelper;
        
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Get array of delivery boy location (formatted in latitude||longitude )
     *
     * @return array
     */
    public function getDeliveryBoyCollection()
    {
        $deliveryboyCollection = $this->deliveryboyResourceCollectionFactory->create();
        $deliveryboyCollection->addFieldToFilter(
            "status",
            1
        );
        $deliveryboyLocations = [];
        foreach ($deliveryboyCollection as $each) {
            $deliveryboyLocations[] = $each->getLatitude() . "||" . $each->getLongitude();
        }
        return $deliveryboyLocations;
    }

    /**
     * Function to get order count assigned to deliveryboy
     *
     * @param int $deliveryboyId id of deliveryboy
     * @return int
     */
    public function getOrderCount($deliveryboyId)
    {
        $tableName  = $this->resource->getTableName("sales_order_grid");
        $assignedOrderCollection = $this->deliveryboyOrderResourceCollectionFactory
            ->create()
            ->addFieldToFilter("deliveryboy_id", $deliveryboyId);
        $assignedOrderCollection->getSelect()
            ->join(
                [
                    "salesOrder"=>$tableName
                ],
                "main_table.order_id=salesOrder.entity_id AND salesOrder.status != 'complete'",
                []
            );
        return count($assignedOrderCollection);
    }

    /**
     * @return string
     */
    public function getGoogleMapApiKey()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            self::CONFIGURATION_PATH_GOOGLE_MAP_KEY,
            $storeScope
        );
    }

    /**
     * @return array
     */
    public function getWarehouseCoordinates(): array
    {
        return [
            'latitude' => $this->deliveryboyHelper->getLatitude(),
            'longitude' => $this->deliveryboyHelper->getLongitude(),
        ];
    }
}
