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

use Magento\Downloadable\Model\Link\Purchased\Item;

class Index extends DownloadAbstract
{
    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $downloadableLink;

    /**
     * @var \Magento\Downloadable\Model\Link\Purchased\Item
     */
    protected $linkPurchasedItem;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $downloadableHelperFile;

    /**
     * @var \Magento\Downloadable\Helper\Download
     */
    protected $downloadableHelperDownload;

    /**
     * Index constructor
     *
     * @param \Magento\Framework\App\Action\Context           $context                    context
     * @param \Magento\Downloadable\Model\Link                $downloadableLink           downloadableLink
     * @param \Magento\Downloadable\Helper\File               $downloadableHelperFile     downloadableHelperFile
     * @param \Magento\Downloadable\Helper\Download           $downloadableHelperDownload downloadableHelperDownload
     * @param \Magento\Downloadable\Model\Link\Purchased\Item $linkPurchasedItem          linkPurchasedItem
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Downloadable\Model\Link $downloadableLink,
        \Magento\Downloadable\Helper\File $downloadableHelperFile,
        \Magento\Downloadable\Helper\Download $downloadableHelperDownload,
        \Magento\Downloadable\Model\Link\Purchased\Item $linkPurchasedItem
    ) {
        $this->downloadableLink = $downloadableLink;
        $this->linkPurchasedItem = $linkPurchasedItem;
        $this->downloadableHelperFile = $downloadableHelperFile;
        $this->downloadableHelperDownload = $downloadableHelperDownload;
        parent::__construct($context);
    }

    /**
     * Execute function for Index Class
     *
     * @return void
     */
    public function execute()
    {
        $wholeData = $this->getRequest()->getParams();
        $linkFile = "";
        $fileName = "";
        $hash = $wholeData["hash"];
        $linkPurchasedItem = $this->linkPurchasedItem->load($hash, "link_hash");
        $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought() -
            $linkPurchasedItem->getNumberOfDownloadsUsed();
        $status = $linkPurchasedItem->getStatus();
        if ($status == Item::LINK_STATUS_AVAILABLE &&
            ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)
        ) {
            if ($linkPurchasedItem->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                $linkFile = $this->downloadableHelperFile->getFilePath(
                    $this->downloadableLink->getBasePath(),
                    $linkPurchasedItem->getLinkFile()
                );
                $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
                $helper = $this->downloadableHelperDownload;
                $helper->setResource($linkFile, $resourceType);
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
                    $this->getResponse()->setHeader(
                        "Content-Disposition",
                        $contentDisposition . "; filename=" . $fileName
                    );
                }
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();
                $helper->output();
                $linkPurchasedItem->setNumberOfDownloadsUsed($linkPurchasedItem->getNumberOfDownloadsUsed() + 1);
                if ($linkPurchasedItem->getNumberOfDownloadsBought() != 0 && !($downloadsLeft - 1)) {
                    $linkPurchasedItem->setStatus(\Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED);
                }
                $linkPurchasedItem->save();
            }
        }
    }
}
