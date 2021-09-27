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
use Magento\Customer\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Session\SessionManager;

class Rmacancel extends \Webkul\Rmasystem\Controller\GuestFrontController
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
        $data = $this->session->getGuestData();
        $model = $this->rmaRepository->getById($id);
        if ($model->getGuestEmail() == $data["email"] && isset($data)) {
            $model->setStatus(4);
            $model->setFinalStatus(1);
            $this->rmaRepository->save($model);

            $message = '<b><span>'.__("RMA Request Cancelled").'</span></b><br/><p class="msg-content">'.
              __('Your RMA request has been cancelled successfully.').'</p>';
            $this->saveRmaHistory($id, $message);
            $this->_emailHelper->cancelRmaEmail($model);

            $this->messageManager->addSuccess(
                __('RMA with id ').$id.__(' has been cancelled successfully')
            );
            return $resultRedirect->setPath('*/guest/rmalist');
        } else {
            $this->messageManager->addError(
                __('Sorry You Are Not Authorised to cancel this RMA request')
            );
            return $resultRedirect->setPath('*/guest/rmalist');
        }
    }
}
