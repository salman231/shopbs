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
namespace Webkul\Rmasystem\Model\Allrma;

use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image
{
    /**
     * media sub folder
     * @var string
     */
    protected $subDir = 'webkul/rmasystem/RMA';
    /**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;
    /**
     * @param UrlInterface $urlBuilder
     * @param Filesystem $fileSystem
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Filesystem $fileSystem
    ) {
    
        $this->urlBuilder = $urlBuilder;
        $this->fileSystem = $fileSystem;
    }
    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseDirRead()
    {
        return $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/');
    }
    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->subDir.'/';
    }
    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir($lastRmaId)
    {
        return $this->fileSystem
            ->getDirectoryWrite(DirectoryList::MEDIA)
            ->getAbsolutePath(
                $this->subDir.'/'.$lastRmaId.'/image/'
            );
    }

    public function getBarcodeDir()
    {
        return $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath($this->subDir.'/Barcodes/');
    }
    /**
     * get images base url
     *
     * @return string
     */
    public function getBarcodeBaseUrl()
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$this->subDir.'/Barcodes/';
    }
}
