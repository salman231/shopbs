<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedCommission\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;

class Check extends Action
{
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
    
        $this->_pageFactory = $pageFactory;
        $this->jsonHelper=$jsonHelper;
        return parent::__construct($context);
    }

    /**
     * Check If ajax executes for set category commission.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode(true)
        );
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
