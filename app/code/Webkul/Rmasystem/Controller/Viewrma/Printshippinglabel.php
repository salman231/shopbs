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

class Printshippinglabel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $shippingLabel;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Webkul\Rmasystem\Model\ShippinglabelFactory $shippingLabel,
        PageFactory $resultPageFactory
    ) {
        $this->shippingLabel = $shippingLabel;
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
        $resultPage->getConfig()->getTitle()->set(__('Pre Shipping Label'));
        if ($model->getCustomerId() != $customerId) {
            $this->_redirect->getRefererUrl();
            $this->messageManager->addError(
                __('Sorry You Are Not Authorised to print this RMA request')
            );
        }
        $shippinig_label_id = $model->getShippingLabel();
        $collection = $this->shippingLabel->create()->getCollection()
                            ->addFieldToFilter('id', $shippinig_label_id);
        if (!$collection->getSize()) {
            $this->messageManager->addError(
                __('Shipping label is not available.')
            );
            return $this->resultRedirectFactory->create()->setPath(
                'rmasystem/viewrma/index',
                ['id'=>$id, '_secure' => $this->getRequest()->isSecure()]
            );
        }
         
        return $resultPage;
    }
}
