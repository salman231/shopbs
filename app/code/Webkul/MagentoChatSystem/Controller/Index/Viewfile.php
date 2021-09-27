<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Controller\Index;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Helper\File\Storage;

class Viewfile extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $_urlDecoder;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     * @param Filesystem $filesystem
     * @param Storage $storage
     * @param File $file
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        Filesystem $filesystem,
        Storage $storage,
        File $file
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->_urlDecoder  = $urlDecoder;
        $this->_fileFactory = $fileFactory;
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->storage = $storage;
    }

    /**
     * Customer view file action
     *
     * @return \Magento\Framework\Controller\ResultInterface|void
     * @throws NotFoundException
     */
    public function execute()
    {
        $file = null;
        $plain = false;
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file = $this->_urlDecoder->decode(
                $this->getRequest()->getParam('file')
            );
        } elseif ($this->getRequest()->getParam('image')) {
            // show plain image
            $file = $this->_urlDecoder->decode(
                str_replace('/', '', $this->getRequest()->getParam('image'))
            );
            
            $plain = true;
        } else {
            throw new NotFoundException(__('Page not found.'));
        }

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->filesystem;
        $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $fileName = 'chatsystem/attachments/' . ltrim($file, '/');
        $path = $directory->getAbsolutePath($fileName);
        if (!$directory->isFile($fileName)
            && !$this->storage->processStorageFile($path)
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        if ($plain) {
            $info = $this->file->getPathInfo($path);
            $extension = $info['extension'];
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }
            $stat = $directory->stat($fileName);
            $contentLength = $stat['size'];
            $contentModify = $stat['mtime'];

            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify));
            $resultRaw->setContents($directory->readFile($fileName));
            return $resultRaw;
        } else {
            $info = $this->file->getPathInfo($path);
            $name = $info['basename'];
            $this->_fileFactory->create(
                $name,
                ['type' => 'filename', 'value' => $fileName],
                DirectoryList::MEDIA
            );
        }
    }
}
