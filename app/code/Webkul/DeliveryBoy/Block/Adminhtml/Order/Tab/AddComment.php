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
 
class AddComment extends \Magento\Backend\Block\Template implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'order/add_comment.phtml';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderResColF;

   /**
    * @param \Webkul\DeliveryBoy\Helper\Data $helperData
    * @param \Magento\Backend\Block\Template\Context $context
    * @param \Magento\Framework\Registry $registry
    * @param \Magento\Sales\Model\OrderFactory $orderFactory
    * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    * @param array $data
    */
    public function __construct(
        \Webkul\DeliveryBoy\Helper\Data $helperData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResColF,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->timezone = $timezone;
        $this->_coreRegistry = $registry;
        $this->orderFactory = $orderFactory;
        $this->deliveryboyOrderResColF = $deliveryboyOrderResColF;
        parent::__construct($context, $data);
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
        return __('Add Comment to Deliveryboy Order');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return __('Add Comment to Deliveryboy Order');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $order = $this->orderFactory->create()->loadByIncrementId($this->getOrderIncrementId());
        
        $allowedShipping = explode(",", $this->helperData->getAllowedShipping());
        if (!in_array($order->getShippingMethod(), $allowedShipping)) {
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

    public function getDeliveryboyOrder()
    {
        $collection = $this->deliveryboyOrderResColF->create()
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
