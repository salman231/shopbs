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
namespace Webkul\Rmasystem\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\SessionManager;

class Printlabel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Session
     */
    protected $session;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    protected $shippingLabel;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\Rmasystem\Model\ShippinglabelFactory $shippingLabel,
        SessionManager $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->session = $session;
        $this->shippingLabel = $shippingLabel;
        parent::__construct($context);
    }

    /**
     * Print Guest rma.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->get('Webkul\Rmasystem\Model\Allrma')->load($id);
        $resultPage->getConfig()->getTitle()->set(__('Pre Shipping Label'));
        $guestSession = $this->session->getGuestData();
        if ($guestSession['email'] == '') {
            $this->messageManager->addError(
                __('You are not authorized to print this RMA.')
            );
            return $resultRedirect->setPath('*/guest/login');
        }
        $shippinig_label_id = $model->getShippingLabel();
        $collection = $this->shippingLabel->create()->getCollection()
                            ->addFieldToFilter('id', $shippinig_label_id);
        if (!$collection->getSize()) {
            $this->messageManager->addError(
                __('Shipping label is not available.')
            );
            return $this->resultRedirectFactory->create()->setPath(
                'rmasystem/guest/rmaview',
                ['id'=>$id, '_secure' => $this->getRequest()->isSecure()]
            );
        }
        $this->coreRegistry->register('guest_data', $guestSession);
        return $resultPage;
    }
}
