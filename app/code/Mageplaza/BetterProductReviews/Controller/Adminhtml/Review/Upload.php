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

namespace Mageplaza\BetterProductReviews\Controller\Adminhtml\Review;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Mageplaza\BetterProductReviews\Helper\Image;

/**
 * Class Upload
 *
 * @package Mageplaza\BetterProductReviews\Controller\Adminhtml\Review
 */
class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Review::reviews_all';

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * Upload constructor.
     *
     * @param Action\Context $context
     * @param RawFactory $resultRawFactory
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param Image $imageHelper
     */
    public function __construct(
        Action\Context $context,
        RawFactory $resultRawFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        Image $imageHelper
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_fileSystem = $filesystem;
        $this->_imageHelper = $imageHelper;

        parent::__construct($context);
    }

    /**
     * @return Raw
     */
    public function execute()
    {
        try {
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
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        /**
         * @var Raw $response
         */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(Image::jsonEncode($result));

        return $response;
    }
}
