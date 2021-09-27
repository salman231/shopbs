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

class CommentGrid extends \Magento\Backend\Block\Widget\Grid\Extended implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Comment\CollectionFactory
     */
    protected $commentCollectionFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Webkul\DeliveryBoy\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory
     */
    protected $deliveryboyOrderResColF;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Webkul\DeliveryBoy\Helper\Data $helper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Webkul\DeliveryBoy\Model\ResourceModel\Comment\CollectionFactory $commentCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Webkul\DeliveryBoy\Helper\Data $helper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Webkul\DeliveryBoy\Model\ResourceModel\Comment\CollectionFactory $commentCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Webkul\DeliveryBoy\Model\ResourceModel\Order\CollectionFactory $deliveryboyOrderResColF,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->dateTime = $dateTime;
        $this->_coreRegistry = $coreRegistry;
        $this->helper = $helper;
        $this->commentCollectionFactory = $commentCollectionFactory;
        $this->deliveryboyOrderResColF = $deliveryboyOrderResColF;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('deliveryboy_comments');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $commentCollection = $this->commentCollectionFactory->create();
        $commentCollection->addFieldToFilter("order_increment_id", $this->getOrderIncrementId());
        $commentCollection->setOrder("created_at", "DESC");
        $this->setCollection($commentCollection);

        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('Comment Id'),
                'sortable' => true,
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'comment',
            [
                'header' => __('Comment'),
                'index' => 'comment'
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Date Time'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );

        $this->addColumn(
            'commented_by',
            [
                'header' => __('Commented By'),
                'index' => 'commented_by',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'expressdelivery/orders/commentGridData',
            [
                '_current' => true,
            ]
        );
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
        return __('Deliveryboy Order Comments');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return __('Deliveryboy Order Comments');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $order = $this->orderFactory->create()->loadByIncrementId($this->getOrderIncrementId());
        
        $allowedShipping = explode(",", $this->helper->getConfigData("deliveryboy/configuration/allowed_shipping"));
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
