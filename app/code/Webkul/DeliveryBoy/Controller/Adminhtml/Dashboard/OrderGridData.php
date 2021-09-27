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
namespace Webkul\DeliveryBoy\Controller\Adminhtml\Dashboard;

class OrderGridData extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Grid Action.
     * Display list of products related to current customer.
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \Webkul\DeliveryBoy\Block\Adminhtml\Edit\Account\Tab\OrderGrid::class,
                'template.order.grid'
            )->toHtml()
        );
    }
}
