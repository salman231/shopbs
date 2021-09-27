<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Rmasystem\Controller\Viewrma;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Printrma extends \Magento\Framework\App\Action\Action
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
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     *
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $customerId = $this->_objectManager->create('Magento\Customer\Model\Session')->getCustomerId();
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->get('Webkul\Rmasystem\Model\Allrma')->load($id);
        $resultPage->getConfig()->getTitle()->set(__('RMA DETAILS'));
        if ($model->getCustomerId() != $customerId) {
            $this->_redirect->getRefererUrl();
            $this->messageManager->addError(
                __('Sorry You Are Not Authorised to print this RMA request')
            );
        }
        return $resultPage;
    }
}
