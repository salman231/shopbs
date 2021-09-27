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
namespace Webkul\Rmasystem\Controller\Adminhtml\Shippinglabel;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{

    protected $uploadModel = 'Webkul\Rmasystem\Model\Upload';

    /**
     * @var \Webkul\Rmasystem\Api\Data\ShippinglabelInterfaceFactory
     */
    protected $shippingLabelDataFactory;

    /**
     * @var \Webkul\Rmasystem\Api\ShippingLabelRepositoryInterface
     */
    protected $shippingLabelRepository;
    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        \Webkul\Rmasystem\Api\Data\ShippinglabelInterfaceFactory $shippingLabelDataFactory,
        \Webkul\Rmasystem\Api\ShippingLabelRepositoryInterface $shippingLabelRepository
    ) {
        $this->shippingLabelDataFactory = $shippingLabelDataFactory;
        $this->shippingLabelRepository = $shippingLabelRepository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Rmasystem::saveshippinglabel');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $id = $this->getRequest()->getParam('id');
        $imageName = '';
        if ($this->getRequest()->getFiles('filename')['error'] == 0) {
            $imageModel = $this->_objectManager->create('Webkul\Rmasystem\Model\Shippinglabel\Image');
            $imageName = $this->uploadFileAndGetName('filename', $imageModel->getBaseDir(), $data);
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $data['filename'] = $imageName;
            if ($id) {
                $model = $this->shippingLabelRepository->getById($id);
            } else {
                $model = $this->shippingLabelDataFactory->create();
            }
            if ($imageName == '' && is_null($id)) {
                $this->messageManager->addError(__('Please upload an image for shipping label.'));
                if ($model->getId()) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                } else {
                    return $resultRedirect->setPath('*/*/new');
                }
            } elseif ($data['filename'] != '') {
                $data['filename'] = $imageName;
            } else {
                unset($data['filename']);
            }
            $model->setData($data)->save();
            try {
                $model = $this->shippingLabelRepository->save($model);

                $this->messageManager->addSuccess(__('Shipping label saved successfully.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Shipping Label.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Upload rma image
     * @param  array $input
     * @param  string $destinationFolder
     * @param  array $data
     * @return string
     */
    public function uploadFileAndGetName($input, $destinationFolder, $data)
    {
        try {
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => $input]
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $uploader->setAllowCreateFolders(true);
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
            ->getDirectoryRead(DirectoryList::MEDIA);
            $result = $uploader->save($destinationFolder);
            return $result['file'];
        } catch (\Exception $e) {
            return '';
        }
        return '';
    }
}
