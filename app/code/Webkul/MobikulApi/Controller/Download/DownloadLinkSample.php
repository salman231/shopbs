<?php
/**
 * Webkul Software.
 *
 *
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Download;

class DownloadLinkSample extends DownloadAbstract
{
    protected $downloadableLink;
    protected $downloadableHelperFile;
    protected $downloadableHelperDownload;

    /**
     * initialize dependencies
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Webkul\MobikulCore\Helper\Catalog $helperCatalog
     * @param \Magento\Downloadable\Model\Link $downloadableLink
     * @param \Magento\Downloadable\Helper\File $downloadableHelperFile
     * @param \Magento\Downloadable\Helper\Download $downloadableHelperDownload
     * @param \Magento\Downloadable\Model\SampleFactory $downloadableSampleLink
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\MobikulCore\Helper\Catalog $helperCatalog,
        \Magento\Downloadable\Model\LinkFactory $downloadableLink,
        \Magento\Downloadable\Helper\File $downloadableHelperFile,
        \Magento\Downloadable\Helper\Download $downloadableHelperDownload,
        \Magento\Downloadable\Model\SampleFactory $downloadableSampleLink
    ) {
        $this->helperCatalog = $helperCatalog;
        $this->downloadableLink = $downloadableLink;
        $this->downloadableHelperFile = $downloadableHelperFile;
        $this->downloadableHelperDownload = $downloadableHelperDownload;
        $this->downloadableSampleLink = $downloadableSampleLink;
        parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $linkId = $data["linkId"] ?? '';
        $sampleId = $data["sampleId"] ?? '';
        $sampleFile = '';
        $samplePath = '';
        $link = '';
        if ($linkId && $sampleId) {
            $samplePath = $this->downloadableSampleLink->create()->getBasePath();
            $link = $this->downloadableSampleLink->create()->load($sampleId);
            $sampleFile = $link->getSampleFile();

        } else {
            if ($linkId) {
                $link = $this->downloadableLink->create()->load($linkId);
                $samplePath = $this->downloadableLink->create()->getBaseSamplePath();
                if (!$link->getSampleFile()) {
                    return;
                }
            } elseif ($sampleId) {
                $samplePath = $this->downloadableSampleLink->create()->getBasePath();
                $link = $this->downloadableSampleLink->create()->load($sampleId);
                $sampleFile = $link->getSampleFile();

            } else {
                return;
            }
        }

        $sampleLinkFilePath = $this->downloadableHelperFile->getFilePath($samplePath, $sampleFile);
        $sampleLinkFilePath = $this->helperCatalog->getBasePath()."/".$sampleLinkFilePath;
        $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
        $helper = $this->downloadableHelperDownload;
        $helper->setResource($sampleLinkFilePath, $resourceType);
        $fileName = $helper->getFilename();
        $contentType = $helper->getContentType();
        $this->getResponse()->setHttpResponseCode(200)
            ->setHeader("Pragma", "public", true)
            ->setHeader("Cache-Control", "must-revalidate, post-check=0, pre-check=0", true)
            ->setHeader("Content-type", $contentType, true);
        if ($fileSize = $helper->getFileSize()) {
            $this->getResponse()->setHeader("Content-Length", $fileSize);
        }
        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()->setHeader("Content-Disposition", $contentDisposition . "; filename=" . $fileName);
        }
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        $helper->output();
    }
}
