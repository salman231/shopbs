<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulCore
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulCore\Model\Product\Attribute\Backend;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Ar model
 */
class Ar extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * Construct
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_file = $file;
        $this->_logger = $logger;
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->_fileUploaderFactory = $fileUploaderFactory;
    }

    public function afterSave($object)
    {
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            "catalog/product/ar/"
        ).$object->getId();
        $delete = $object->getData($this->getAttribute()->getName() . "_delete");
        if ($delete) {
            $exploadedFileArr = explode("/", $object->getData($this->getAttribute()->getName()));
            $fileName = $exploadedFileArr[count($exploadedFileArr) - 1];
            if ($this->_file->isExists($path."/".$fileName)) {
                $this->_file->deleteFile($path."/".$fileName);
            }
            $object->setData($this->getAttribute()->getName(), "");
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        }

        $this->_logger->debug($path);
        try {
            $uploader = $this->_fileUploaderFactory->create(
                ["fileId" => "product[".$this->getAttribute()->getName()."]"]
            );
            if ($uploader->validateFile()) {
                return $this;
            }
            if ($this->getAttribute()->getName() == "ar_2d_file") {
                $uploader->setAllowedExtensions(["png"]);
            } elseif ($this->getAttribute()->getName() == "ar_model_file_android") {
                $uploader->setAllowedExtensions(["sfb"]);
            } else {
                $uploader->setAllowedExtensions(["usdz"]);
            }
            $uploader->setAllowRenameFiles(true);
            $result = $uploader->save($path);
            $path = $this->getMediaUrl()."catalog/product/ar/".$object->getId()."/".$result["file"];
            $object->setData($this->getAttribute()->getName(), $path);
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                $this->_logger->critical($e);
            }
        }
        return $this;
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
