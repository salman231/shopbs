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
namespace Webkul\DeliveryBoy\Block\Adminhtml\Order\Tab;

class AssignDeliveryBoy extends \Magento\Backend\Block\Template implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helper;

    /**
     * @var string
     */
    protected $_template = 'order/assign_deliveryboy.phtml';

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     */
    protected $deliveryboyResourceCollectionFactory;

    /**
     * @var $deliveryboyOrderResourceCollectionFactory
     */
    protected $deliveryboyOrderResourceCollectionFactory;

    /**
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory
     *        $deliveryboyResourceCollectionFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     *        $deliveryboyOrderResourceCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\DeliveryBoy\Model\ResourceModel\Deliveryboy\CollectionFactory $deliveryboyResourceCollectionFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResourceCollectionFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->_coreRegistry = $registry;
        $this->jsonHelper = $jsonHelper;
        $this->orderFactory = $orderFactory;
        $this->deliveryboyResourceCollectionFactory = $deliveryboyResourceCollectionFactory;
        $this->deliveryboyOrderResourceCollectionFactory = $deliveryboyOrderResourceCollectionFactory;

        parent::__construct($context, $data);
    }

    /**
     * @param array $configData
     * @return string
     */
    public function jsonEncode($configData)
    {
        return $this->jsonHelper->jsonEncode($configData);
    }

    /**
     * @return \Magento\Sales\Api\OrderInterface
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getOrder()->getEntityId();
    }

    /**
     * @return int
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Assign DeliveryBoy');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return __('Assign DeliveryBoy');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $order = $this->orderFactory->create()->loadByIncrementId(
            $this->getOrderIncrementId()
        );

        $allowedShipping = explode(
            ",",
            $this->helper->getAllowedShipping()
        );
        if (!in_array(
            $order->getShippingMethod(),
            $allowedShipping
        )
        ) {
            return false;
        }
        if (!$this->getDeliveryboyOrder()->getId()) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getDeliveryBoyList()
    {
        $deliveryboyCollection = $this->deliveryboyResourceCollectionFactory->create();

        $deliveryboyCollection->addFieldToFilter("status", 1)
            ->addFieldToFilter("availability_status", 1)
            ->setOrder("created_at", "ASC");
        $this->_eventManager->dispatch(
            'wk_deliveryboy_deliveryboy_collection_apply_filter_event',
            [
                'deliveryboy_collection' => $deliveryboyCollection,
                'collection_table_name' => 'main_table',
            ]
        );
        $deliveryboyList = [];
        foreach ($deliveryboyCollection as $each) {
            $eachDeliveryboy = [];
            $eachDeliveryboy["id"] = $each->getId();
            $eachDeliveryboy["name"] = $each->getName();
            $eachDeliveryboy["status"] = $each->getStatus();
            $eachDeliveryboy[
                "availabilityStatus"
            ] = (bool)$each->getAvailabilityStatus();
            $deliveryboyList[] = $eachDeliveryboy;
        }
        return $deliveryboyList;
    }

    /**
     * @return int
     */
    public function getAssignedDeliveryBoy()
    {
        $deliveryBoyOrder = $this->getDeliveryboyOrder();
        if ($deliveryBoyOrder->getId()) {
            return $deliveryBoyOrder->getDeliveryboyId() ?? 0;
        }
        return 0;
    }

    public function getDeliveryboyOrder()
    {
        $collection = $this->deliveryboyOrderResourceCollectionFactory->create()
            ->addFieldToFilter(
                "increment_id",
                $this->getOrderIncrementId()
            );
        $this->_eventManager->dispatch(
            'wk_deliveryboy_assigned_order_collection_apply_filter_event',
            [
                'deliveryboy_order_collection' => $collection,
                'collection_table_name' => 'main_table',
                'owner_id' => 0,
            ]
        );
        $deliveryBoyOrder = $collection->getFirstItem();
        return $deliveryBoyOrder;
    }
}
