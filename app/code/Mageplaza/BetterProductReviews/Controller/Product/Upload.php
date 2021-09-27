<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_BetterProductReviews
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterProductReviews\Controller\Product;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\File\Size;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Mageplaza\BetterProductReviews\Helper\Image;

/**
 * Class Upload
 *
 * @package Mageplaza\ProductAttachments\Controller\Adminhtml\File\Attachment
 */
class Upload extends Action
{
    /**
     * @var Size
     */
    protected $_fileSize;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var Json
     */
    protected $_resultJson;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Size $fileSize
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param Json $resultJson
     * @param Image $imageHelper
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Size $fileSize,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        Json $resultJson,
        Image $imageHelper
    ) {
        $this->_resultPageFactory = $pageFactory;
        $this->_fileSize = $fileSize;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_fileSystem = $filesystem;
        $this->_resultJson = $resultJson;
        $this->_imageHelper = $imageHelper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $page = $this->_resultPageFactory->create();
        $layout = $page->getLayout();
        $currentDate = $this->getRequest()->getParam('value_id');
        $position = (int)$this->getRequest()->getParam('position');
        $maxImageSize = $this->_fileSize->getMaxFileSizeInMb();

        try {
            /**
             * Upload file to media directory
             */
            $uploader = $this->_uploaderFactory->create(['fileId' => 'image']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            /**
             * @var Read $mediaDirectory
             */
            $mediaDirectory = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA);
            $result = $uploader->save($mediaDirectory
                ->getAbsolutePath($this->_imageHelper->getBaseTmpMediaPath()));

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = $this->_imageHelper->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';

            /**
             * Set return data to file gallery
             */
            $data = [
                'file_id' => $currentDate,
                'label' => $result['name'],
                'file' => $result['file'],
                'url' => $result['url'],
                'position' => $position + 1
            ];
            $this->_eventManager->dispatch(
                'mpbetterproductreviews_review_gallery_upload_image_after',
                ['result' => $data, 'action' => $this]
            );
            $response = [
                'review_images' => $layout->createBlock('Magento\Framework\View\Element\Template')
                    ->setTemplate('Mageplaza_BetterProductReviews::review/form/images.phtml')
                    ->setFileData($data)
                    ->toHtml(),
                'success' => true
            ];
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
                'errorSize' => __('Make sure your file isn\'t more than %1M.', $maxImageSize)
            ];
        }

        return $this->_resultJson->setData($response);
    }
}
