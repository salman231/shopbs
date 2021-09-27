<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpreportsystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpreportsystem\Block\Adminhtml\Mpreport;

use Webkul\Mpreportsystem\Block\Adminhtml\Mpreport;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Webkul\Mpreportsystem\Block\Adminhtml\Mpreport\Grid
     */
    protected $_mpreportBlock;

    /**
     * @var Magento\Framework\DataObject
     */
    protected $_dataObject;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param \Magento\Framework\Registry             $coreRegistry
     * @param Mpreport                                $blockMpreport
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        Mpreport $blockMpreport,
        \Magento\Framework\DataObject $dataObject,
        array $data = []
    ) {
        $this->_dataObject = $dataObject;
        $this->_mpreportBlock = $blockMpreport;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('salesgrid');
        $this->setDefaultSort('total_seller_amount');
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $data = $this->_mpreportBlock->getParamValues();
        $data['filter'] = 'year';
        $collection = $this->_mpreportBlock->getSalesCollection($data);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'row',
            [
                'header'    => __('#'),
                'filter'    => false,
                'sortable'  =>  false,
                'index'     => 'row',
                'renderer'  => \Webkul\Mpreportsystem\Block\Adminhtml\Mpreport\Grid\Renderrow::class,
            ]
        );
        $this->addColumn(
            'order_date',
            [
                'header'    => __('Date'),
                'filter'    => false,
                'index'     => 'order_date',
                'type'      => 'date',
            ]
        );
        $this->addColumn(
            'total_order_id',
            [
                'header'    => __('Total Orders'),
                'filter'    => false,
                'index'     => 'total_order_id'
            ]
        );
        $this->addColumn(
            'total_item_qty',
            [
                'header'    => __('Total Items Sold'),
                'filter'    => false,
                'index'     => 'total_item_qty',
                'type'      => 'text',
            ]
        );
        $this->addColumn(
            'total_seller_amount',
            [
                'header'    => __('Revenues'),
                'filter'    => false,
                'sortable'  =>  true,
                'index'     => 'total_seller_amount',
                'currency'  => 'currency_code',
                'type'      => 'currency',
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return 'javascript:void(0)';
    }

    /**
     * @return void
     */
    public function getcurrency()
    {
        return $currencyCode = $this->_storeManager
            ->getStore()
            ->getBaseCurrencyCode();
    }

    /**
     * @return void
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpreportsystem/report/grid', ['_current' => true]);
    }

    /**
     * get totals of seller amount
     *
     * @return void
     */
    public function getTotals()
    {
        $fields = [
            'total_order_id' => 0,
            'total_item_qty' => 0,
            'total_seller_amount' => 0,
        ];
        foreach ($this->getCollection() as $item) {
            foreach ($fields as $field => $value) {
                $fields[$field] += $item->getData($field);
            }
        }
        return $this->_dataObject->setData($fields);
    }
}
