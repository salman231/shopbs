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
namespace Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab\Feedback;

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
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->getReport('chat_ratings_listing_data_source')->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'agent_id'
        )->addFieldToSelect(
            'customer_id'
        )->addFieldToSelect(
            'rating'
        )->addFieldToSelect(
            'rating_comment'
        )->addFieldToSelect(
            'created_at'
        )->addFieldToSelect(
            'status'
        )->addFieldToFilter(
            'main_table.agent_id',
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
        $this->addColumn('agentname', ['header' => __('Agent Name'), 'width' => '100', 'index' => 'agentname']);

        $this->addColumn(
            'rating',
            [
                'header' => __('Rating Stars'),
                'align' => 'center',
                'index' => 'rating',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'center',
                'filter' => \Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab\Feedback\Grid\Filter\Status::class,
                'index' => 'status',
                'renderer' => \Webkul\MagentoChatSystem\Block\Adminhtml\Edit\Tab\Feedback\Grid\Renderer\Status::class
            ]
        );
        $this->addColumn('rating_comment', ['header' => __('Comment'), 'width' => '100', 'index' => 'rating_comment']);
        $this->addColumn('created_at', ['header' => __('Date'), 'type'=> 'datetime', 'index' => 'created_at']);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('chatsystem/agent_view/feedback', ['_current' => true]);
    }
}
