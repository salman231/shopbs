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

namespace Webkul\Mpreportsystem\Controller\Adminhtml\Report;

use Magento\Customer\Controller\RegistryConstants;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context               $context
     * @param \Magento\Framework\Controller\Result\RawFactory   $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory             $layoutFactory
     * @param \Magento\Framework\Registry                       $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->_resultRawFactory = $resultRawFactory;
        $this->_layoutFactory = $layoutFactory;
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
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->_resultRawFactory->create();
        return $resultRaw->setContents(
            $this->_layoutFactory->create()->createBlock(
                \Webkul\Mpreportsystem\Block\Adminhtml\Mpreport\Grid::class,
                'salesgrid'
            )->toHtml()
        );
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpreportsystem::mpreports');
    }
}
