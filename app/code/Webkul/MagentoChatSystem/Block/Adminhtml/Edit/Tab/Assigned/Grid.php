<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MagentoChatSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab\Assigned;

/**
 * Adminhtml agent assigned chat grid block
 *
 * @api
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('chatsystem_agent_grid');
        $this->setDefaultSort('entity_id', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Get Agent Uniqe Id
     *
     * @return int
     */
    public function getAgentUniqeId()
    {
        return $this->_coreRegistry->registry('agent_data')->getAgentUniqueId();
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->getReport('assigned_chat_listing_data_source')->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'agent_id'
        )->addFieldToSelect(
            'customer_id'
        )->addFieldToSelect(
            'chat_status'
        )->addFieldToFilter(
            'agent_id',
            $this->_coreRegistry->registry('agent_data')->getAgentId()
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', ['header' => __('Customer Name'), 'width' => '100', 'index' => 'name']);

        $this->addColumn(
            'chat_status',
            [
                'header' => __('Chat Status'),
                'align' => 'center',
                'filter' => \Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab\Assigned\Grid\Filter\Status::class,
                'index' => 'chat_status',
                'renderer' => \Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab\Assigned\Grid\Renderer\Status::class
            ]
        );

        $this->addColumn(
            'edit',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View History'),
                        'url' => [
                            'base' => 'chatsystem/message/index',
                            'params' => ['agent_unique_id' => $this->getAgentUniqeId()]
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('chatsystem/agent_view/assignedchat', ['_current' => true]);
    }
}
