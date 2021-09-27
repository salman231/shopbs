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
use Magento\Customer\Model\Session;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\Rmasystem\Model\ResourceModel\Rmaitem\CollectionFactory as ItemCollectionFactory;
use Magento\Sales\Model\OrderRepository;
use Webkul\Rmasystem\Api\AllRmaRepositoryInterface;
use Webkul\Rmasystem\Api\Data\AllrmaInterfaceFactory;

class Update extends \Webkul\Rmasystem\Controller\FrontController
{

    /**
     * Update action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $post = $this->getRequest()->getPost();

        if ($post && $this->getRequest()->getParam('rma_id')) {
            $statusFlag = false;
            $deliveryFlag = false;
            $error = false;
            $customerId = $this->_customerSession->getCustomerId();
            $model = $this->rmaRepository->getById($post['rma_id']);
            $attachment = $this->getRequest()->getFiles('attachment');
            $fileName = '';
            if (isset($attachment['error']) && !$attachment['error']) {
                $result = $this->uploadConversationFile('attachment', $post['rma_id']);
                if ($result['error'] == 1) {
                    return $resultRedirect->setPath('*/viewrma/index', ['id' => $post['rma_id']]);
                } else {
                    $fileName = $result['file'];
                }
            }
            $isUploadImages = $this->getRequest()->getFiles('related_images');
            $lastRmaId = $model->getId();
            if ($isUploadImages && count($isUploadImages)) {
                try {
                    if (isset($post['total_images']) && $post['total_images'] > 0) {
                        $imageArray = $this->saveRmaProductImage($post['total_images'], $lastRmaId);
                        $model->setImage(serialize($imageArray));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Unsupported image(s) type.'));
                    return $resultRedirect->setPath('*/viewrma/index', ['id' => $post['rma_id']]);
                }
            }
            
            $post["message"] = preg_replace('/<[^>]*>/', '', $post["message"]);
            if (trim($post["message"]) != '') {
                $conversationModel = $this->conversationDataFactory->create()
                  ->setRmaId($post['rma_id'])
                  ->setMessage($post['message'])
                  ->setAttachment($fileName)
                  ->setCreatedAt(time())
                  ->setSender('customer');
                try {
                    $this->conversationRepository->save($conversationModel);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                    return $resultRedirect->setPath('*/*/index');
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving the Message.'));
                    return $resultRedirect->setPath('*/*/index');
                }
            }
            if (isset($post['solved'])) {
                $model->setStatus(2);
                $model->setFinalStatus(4);
                $model->setAdminStatus(6);
                $statusFlag = true;
                $message = '<span>'.__("RMA Status Updated.").'</span><br/><br/><p class="msg-content">'.
                  __('RMA status has been changed to Solved.').'</p>';
                $this->saveRmaHistory($lastRmaId, $message);
            }
            if (isset($post['pending'])) {
                $model->setStatus(0);
                $model->setFinalStatus(0);
                $model->setAdminStatus(0);
                $message = '<span>'.__("RMA Status Updated.").'</span><br/><br/><p class="msg-content">'.
                  __('RMA status has been changed to Pending.').'</p>';
                $this->saveRmaHistory($lastRmaId, $message);
                $statusFlag = true;
            }
            if ($model->getCustomerConsignmentNo() != $post['customer_consignment_no']) {
                $model->setCustomerConsignmentNo($post['customer_consignment_no']);
                $deliveryFlag = true;
            }

            try {
                $lastRmaId = $this->rmaRepository->save($model)->getId();
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while updating the RMA.'));
                return $resultRedirect->setPath('*/*/index');
            }

            $selfEmail = [
                            'check' => false,
                            'area' => 'frontend'
                        ];
            if (isset($post['receive_email'])) {
                $selfEmail['check'] = true;
            }
            if ($statusFlag == true || $deliveryFlag == true) {
                $this->_emailHelper->updateRmaEmail($post, $model, $statusFlag, $deliveryFlag, $selfEmail, $fileName);
            } else {
                $this->_emailHelper->newMessageEmail($post, $model, $selfEmail, $fileName);
            }
            $this->messageManager->addSuccess(
                __('RMA Successfully Updated.')
            );

            return $resultRedirect->setPath('*/viewrma/index', ['id' => $post['rma_id']]);
        } else {
            $this->messageManager->addError(__('Unable to save.'));

            return $resultRedirect->setPath('*/viewrma/index', ['id' => $post['rma_id']]);
        }
    }

    protected function saveRmaProductImage($numberOfImages, $lastRmaId)
    {
        $imageArray = [];
        if ($numberOfImages > 0) {
            $path = $this->helper->getBaseDir($lastRmaId);
            for ($i = 0; $i < $numberOfImages; $i++) {
                $fileId = "related_images[$i]";
                $this->uploadImage($fileId, $path, $imageArray);
            }
        }
        return $imageArray;
    }

    /**
     * Upload Image of Rma
     *
     * @param string $fileId
     * @param string $uploadPath
     * @param int $count
     */
    protected function uploadImage($fileId, $path, &$imageArray)
    {
        $extArray = ['jpg','JPG','jpeg','JPEG','gif','GIF','png','PNG','bmp','BMP'];
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions($extArray);
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($path);
            $imageArray[$result['file']] = $result['file'];
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving images.'));
        }
    }
    /**
     * Upload Image of Rma
     *
     * @param string $fileId
     * @param string $uploadPath
     * @param int $count
     */
    protected function uploadConversationFile($fileId, $rmaId)
    {
        $extArray =  explode(',', $this->helper->getConfigData('file_attachment_extension'));
        $path = $this->helper->getConversationDir($rmaId);
        try {
            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions($extArray);
            $uploader->setAllowRenameFiles(true);
            $uploader->setAllowCreateFolders(true);
            $result = $uploader->save($path);
            $fileName = $result['file'];
        } catch (\Exception $e) {
            $result['error'] = 1;
            $e->getMessage();
            $this->messageManager->addException($e, __('Something went wrong while sending attachment.'));
        }
        return $result;
    }
}
