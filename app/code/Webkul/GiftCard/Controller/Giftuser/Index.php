<?php
/**
 * Webkul GiftCard detail controller.
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\GiftCard\Controller\Giftuser;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory
    ) {
        $this->_resultPageFactory = $_resultPageFactory;
        parent::__construct($context);
    }

    /**
     * GiftCard Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('GiftCard Detail Page'));
        return $resultPage;
    }
}
