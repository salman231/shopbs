<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class History extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\ResultFactory $result
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirect = $result;
        parent::__construct($context);
    }

    /**
     * Rma List
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $msgDate = $this->getRequest()->getParam('msg_date');
        if ($msgDate && !date_create($msgDate)) {
            $this->messageManager->addError('Invalid Date');
            $resultRedirect = $this->resultRedirect->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
        if ($block) {
            $block->setRefererUrl($this->_redirect->getRefererUrl());
        }
        return $resultPage;
    }
}
