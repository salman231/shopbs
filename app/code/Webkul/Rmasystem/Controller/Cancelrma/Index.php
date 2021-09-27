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
namespace Webkul\Rmasystem\Controller\Cancelrma;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\Filesystem\Io\File;

class Index extends \Webkul\Rmasystem\Controller\FrontController
{
    /**
     * Cancel action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $customerId = $this->_objectManager->create('Magento\Customer\Model\Session')->getCustomerId();
        $model = $this->rmaRepository->getById($id);
        if ($model->getCustomerId() == $customerId) {
            $model->setStatus(4);
            $model->setFinalStatus(1);
            $model->setAdminStatus(0);
            $this->rmaRepository->save($model);

            $message = '<span>'.__("RMA Request Cancelled").'</span><br/><br/><p class="msg-content">'.
              __('Your RMA request has been cancelled successfully.').'</p>';
            $this->saveRmaHistory($id, $message);
            $this->_emailHelper->cancelRmaEmail($model);
            $this->messageManager->addSuccess(
                __('RMA with id ').$id.__(' has been cancelled successfully')
            );
            return $resultRedirect->setPath('*/index');
        } else {
            $this->messageManager->addError(
                __('Sorry You Are Not Authorised to cancel this RMA request')
            );
            return $resultRedirect->setPath('*/index');
        }
    }
}
