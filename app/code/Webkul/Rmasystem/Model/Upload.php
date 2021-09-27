<?php
namespace Webkul\Rmasystem\Model;

//use Magento\Framework\Model\Exception as FrameworkException;
use Magento\Framework\File\Uploader;
use Magento\Framework\App\Filesystem\DirectoryList;

class Upload
{
    /**
     * uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    protected $messageManager;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
    }

    /**
     * upload file
     *
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    public function uploadFileAndGetName($input, $destinationFolder, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploader = $this->_objectManager->create(
                    \Magento\MediaStorage\Model\File\Uploader::class,
                    ['fileId' => 'image']
                );
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $imageAdapter = $this->_objectManager->get(\Magento\Framework\Image\AdapterFactory::class)->create();
                $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $mediaDirectory = $this->_objectManager->get(\Magento\Framework\Filesystem::class)
                ->getDirectoryRead(DirectoryList::MEDIA);
                $result = $uploader->save($mediaDirectory->getAbsolutePath($destinationFolder));
                return $result['file'];
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return '';
    }
}
