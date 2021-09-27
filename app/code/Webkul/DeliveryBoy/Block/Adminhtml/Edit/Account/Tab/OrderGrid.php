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

class OrderGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        $this->dateTime = $dateTime;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_tab_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->orderFactory->create()->getCollection()->addAttributeToSelect(
            "*"
        )->addAttributeToFilter(
            "shipping_method",
            "expressdeliveryboy_expressdeliveryboy"
        );
        $date = $this->dateTime->gmtDate("Y-m-d");
        $startDate = $date . ' 00:00:01';
        $endDate = $date . ' 23:59:59';
        $collection->addAttributeToFilter(
            'created_at',
            [
                'from' => $startDate,
                'to' => $endDate
            ]
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Order Id'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer Name'),
                'index' => 'customer_firstname'
            ]
        );
        $this->addColumn(
            'grand_total',
            [
                'header' => __('Grand Total'),
                'index' => 'grand_total'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );
        $this->addColumn(
            'store_id',
            [
                'header' => __('Store Id'),
                'index' => 'store_id'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderGridData', ['_current' => true]);
    }
}
